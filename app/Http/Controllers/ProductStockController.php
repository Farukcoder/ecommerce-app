<?php

namespace App\Http\Controllers;

use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HeaderSetting;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockLog;
use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductStockController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function index(Request $request)
    {
        $filters = $this->filters($request);

        $headerSetting = HeaderSetting::query()->latest('id')->first();
        $systemSetting = SystemSetting::query()->latest('id')->first();

        $categories = Category::query()->select('id', 'name')->orderBy('name')->get();
        $brands = Brand::query()->select('id', 'name')->orderBy('name')->get();
        $varieties = AttributeValue::query()
            ->with('attribute:id,name')
            ->select('id', 'attribute_id', 'value')
            ->orderBy('value')
            ->get();

        $productsQuery = $this->inventoryQuery($filters);

        if ($request->query('export') === 'csv') {
            return $this->exportProducts(clone $productsQuery);
        }

        $products = (clone $productsQuery)->paginate(15)->withQueryString();
        $statsProducts = (clone $productsQuery)->get();

        $variantAnalysis = $statsProducts
            ->flatMap(function (Product $product) {
                return $product->stocks->map(function ($stock) use ($product) {
                    $variantLabel = $stock->attributeValue?->attribute?->name
                        ? $stock->attributeValue->attribute->name.': '.$stock->attributeValue->value
                        : ($stock->attributeValue?->value ?? ($stock->sku ?: 'Variant #'.$stock->id));

                    return [
                        'id' => $stock->attribute_value_id ?: $stock->id,
                        'label' => $variantLabel,
                        'qty' => (int) $stock->quantity,
                        'product_id' => $product->id,
                    ];
                });
            })
            ->groupBy('id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'label' => $first['label'],
                    'qty' => $items->sum('qty'),
                    'product_count' => $items->pluck('product_id')->unique()->count(),
                ];
            })
            ->sortByDesc('qty')
            ->values();

        $stats = [
            'total_products' => $statsProducts->count(),
            'total_quantity' => $statsProducts->sum(fn (Product $product) => (int) $product->qty),
            'sold_quantity' => $statsProducts->sum(fn (Product $product) => max(0, (int) ($product->sold_qty ?? 0))),
            'in_stock' => $statsProducts->filter(fn (Product $product) => (int) $product->qty > self::LOW_STOCK_THRESHOLD)->count(),
            'out_of_stock' => $statsProducts->filter(fn (Product $product) => (int) $product->qty === 0)->count(),
            'inventory_value' => $statsProducts->sum(fn (Product $product) => (float) $product->price * (int) $product->qty),
            'variant_types' => $variantAnalysis->count(),
        ];

        $categoryCounts = $this->facetCounts('category', $filters);
        $brandCounts = $this->facetCounts('brand', $filters);
        $varietyCounts = $this->facetCounts('variety', $filters);

        $categories->each(function ($category) use ($categoryCounts) {
            $category->product_count = (int) ($categoryCounts[$category->id] ?? 0);
        });

        $brands->each(function ($brand) use ($brandCounts) {
            $brand->product_count = (int) ($brandCounts[$brand->id] ?? 0);
        });

        $varieties->each(function ($variety) use ($varietyCounts) {
            $variety->product_count = (int) ($varietyCounts[$variety->id] ?? 0);
        });

        $brand = [
            'name' => $systemSetting?->system_name ?? config('app.name'),
            'subtitle' => $systemSetting?->frontend_website_name ?? 'Product inventory',
            'logo' => $headerSetting?->header_logo_url
                ?? $systemSetting?->system_logo_black_url
                ?? $systemSetting?->system_logo_white_url,
        ];

        return view('products.stock', [
            'brand' => $brand,
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'varieties' => $varieties,
            'variantAnalysis' => $variantAnalysis,
            'stats' => $stats,
        ]);
    }

    private function filters(Request $request): array
    {
        $stockStatus = strtolower((string) $request->query('stock_status', 'all'));

        if (! in_array($stockStatus, ['all', 'in', 'low', 'out'], true)) {
            $stockStatus = 'all';
        }

        return [
            'search' => trim((string) $request->query('search', '')),
            'category' => $request->integer('category') ?: null,
            'brand' => $request->integer('brand') ?: null,
            'variety' => $request->integer('variety') ?: null,
            'stock_status' => $stockStatus,
        ];
    }

    private function inventoryQuery(array $filters): Builder
    {
        $inventoryTotals = DB::table('product_stocks')
            ->selectRaw('product_id, COALESCE(SUM(quantity), 0) as qty')
            ->groupBy('product_id');

        $query = Product::query()
            ->select('products.*')
            ->leftJoinSub($inventoryTotals, 'inventory', function ($join) {
                $join->on('inventory.product_id', '=', 'products.id');
            })
            ->addSelect(DB::raw('COALESCE(inventory.qty, 0) as qty'))
            ->leftJoinSub(
                DB::table('stock_logs')
                    ->selectRaw("product_id, COALESCE(SUM(CASE WHEN change_type = 'order' THEN quantity WHEN change_type = 'return' THEN -quantity ELSE 0 END), 0) as sold_qty")
                    ->groupBy('product_id'),
                'sales',
                function ($join) {
                    $join->on('sales.product_id', '=', 'products.id');
                }
            )
            ->addSelect(DB::raw('COALESCE(sales.sold_qty, 0) as sold_qty'))
            ->with([
                'brand:id,name',
                'categories:id,name',
                'stocks' => function ($stockQuery) {
                    $stockQuery->select('id', 'product_id', 'attribute_value_id', 'sku', 'quantity')
                        ->with('attributeValue.attribute:id,name')
                        ->with('attributeValue:id,attribute_id,value');
                },
            ])
            ->orderBy('products.name');

        $this->applyFilters($query, $filters);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters, array $exclude = []): void
    {
        if (! in_array('search', $exclude, true) && $filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%")
                    ->orWhereHas('brand', function (Builder $brandQuery) use ($search) {
                        $brandQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! in_array('category', $exclude, true) && $filters['category']) {
            $query->whereHas('categories', function (Builder $categoryQuery) use ($filters) {
                $categoryQuery->where('categories.id', $filters['category']);
            });
        }

        if (! in_array('brand', $exclude, true) && $filters['brand']) {
            $query->where('products.brand_id', $filters['brand']);
        }

        if (! in_array('variety', $exclude, true) && $filters['variety']) {
            $query->whereHas('attributeValues', function (Builder $varietyQuery) use ($filters) {
                $varietyQuery->where('attribute_values.id', $filters['variety']);
            });
        }

        if (! in_array('stock_status', $exclude, true) && $filters['stock_status'] !== 'all') {
            $expression = $this->stockQuantityExpression();

            if ($filters['stock_status'] === 'in') {
                $query->whereRaw("{$expression} > ?", [self::LOW_STOCK_THRESHOLD]);
            } elseif ($filters['stock_status'] === 'low') {
                $query->whereRaw("{$expression} between ? and ?", [1, self::LOW_STOCK_THRESHOLD]);
            } elseif ($filters['stock_status'] === 'out') {
                $query->whereRaw("{$expression} = 0");
            }
        }
    }

    private function facetCounts(string $facet, array $filters)
    {
        $query = Product::query();
        $this->applyFilters($query, $filters, [$facet]);

        return match ($facet) {
            'category' => $query
                ->join('category_product', 'category_product.product_id', '=', 'products.id')
                ->join('categories', 'categories.id', '=', 'category_product.category_id')
                ->groupBy('categories.id', 'categories.name')
                ->orderBy('categories.name')
                ->selectRaw('categories.id, categories.name, COUNT(DISTINCT products.id) as product_count')
                ->pluck('product_count', 'id'),
            'brand' => $query
                ->join('brands', 'brands.id', '=', 'products.brand_id')
                ->groupBy('brands.id', 'brands.name')
                ->orderBy('brands.name')
                ->selectRaw('brands.id, brands.name, COUNT(DISTINCT products.id) as product_count')
                ->pluck('product_count', 'id'),
            'variety' => $query
                ->join('product_attribute_value', 'product_attribute_value.product_id', '=', 'products.id')
                ->join('attribute_values', 'attribute_values.id', '=', 'product_attribute_value.attribute_value_id')
                ->groupBy('attribute_values.id', 'attribute_values.value')
                ->orderBy('attribute_values.value')
                ->selectRaw('attribute_values.id, attribute_values.value, COUNT(DISTINCT products.id) as product_count')
                ->pluck('product_count', 'id'),
            default => collect(),
        };
    }

    private function stockQuantityExpression(): string
    {
        return '(select coalesce(sum(quantity), 0) from product_stocks where product_stocks.product_id = products.id)';
    }

    private function exportProducts(Builder $query)
    {
        $rows = (clone $query)->get();

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['Product', 'SKU', 'Category', 'Brand', 'Variety', 'Qty', 'Sold Qty', 'Stock Status', 'Price']);

            foreach ($rows as $product) {
                $varietyValues = $product->stocks
                    ->map(fn ($stock) => $stock->attributeValue?->value)
                    ->filter()
                    ->unique()
                    ->values();

                fputcsv($output, [
                    $product->name,
                    $product->sku,
                    $product->categories->pluck('name')->implode(', '),
                    $product->brand?->name,
                    $varietyValues->implode(', '),
                    $product->qty,
                    max(0, (int) ($product->sold_qty ?? 0)),
                    $product->stock_status,
                    number_format((float) $product->price, 2, '.', ''),
                ]);
            }

            fclose($output);
        }, 'product-inventory.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'action' => 'required|in:increase,decrease',
        ]);

        $quantity = $request->integer('quantity');
        $action = $request->string('action');

        $stock = $product->stocks()->first();

        if (! $stock) {
            return response()->json(['success' => false, 'message' => __('messages.no_stock_found')], 404);
        }

        if ($action === 'decrease' && $stock->quantity < $quantity) {
            return response()->json(['success' => false, 'message' => __('messages.insufficient_stock')], 400);
        }

        if ($action === 'increase') {
            $stock->increaseStock($quantity);
        } else {
            $stock->reduceStock($quantity);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.stock_updated'),
            'new_quantity' => $stock->quantity,
        ]);
    }

    public function stockDetails(Product $product)
    {
        try {
            $currentStock = (int) $product->stocks()->sum('quantity');
            $productPrice = (float) $product->price;

            $variants = $product->stocks()->with(['attributeValue.attribute'])->get()->map(function ($stock) use ($productPrice) {
                $variantLabel = $stock->attributeValue?->attribute?->name
                    ? $stock->attributeValue->attribute->name.': '.$stock->attributeValue->value
                    : ($stock->attributeValue?->value ?? ($stock->sku ?: 'Variant #'.$stock->id));

                return [
                    'id' => $stock->id,
                    'name' => $variantLabel,
                    'sku' => $stock->sku,
                    'quantity' => (int) $stock->quantity,
                    'price' => (float) $stock->price,
                    'attribute_value_id' => $stock->attribute_value_id,
                ];
            });

            $stockHistory = StockLog::where('product_id', $product->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'date' => $log->created_at->format('Y-m-d H:i'),
                        'quantity' => $log->quantity,
                        'type' => $log->change_type,
                    ];
                });

            // Get all available attribute values for variant selection
            $attributeValues = AttributeValue::with('attribute:id,name')
                ->select('id', 'attribute_id', 'value')
                ->orderBy('attribute_id')
                ->orderBy('value')
                ->get()
                ->map(function ($value) {
                    return [
                        'id' => $value->id,
                        'attribute' => [
                            'id' => $value->attribute?->id,
                            'name' => $value->attribute?->name,
                        ],
                        'value' => $value->value,
                    ];
                });

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => (float) $product->price,
                ],
                'current_stock' => $currentStock,
                'variants' => $variants,
                'attribute_values' => $attributeValues,
                'stock_history' => $stockHistory,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function addStock(Request $request)
    {
        $request->validate([
            'variants' => 'required|array',
            'variants.*.stock_id' => 'required|exists:product_stocks,id',
            'variants.*.quantity' => 'required|integer|min:0',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $note = $request->string('note');
        $updatedCount = 0;

        foreach ($request->input('variants') as $variantData) {
            $stock = ProductStock::find($variantData['stock_id']);
            if (! $stock) {
                continue;
            }

            $newQuantity = (int) $variantData['quantity'];
            $newPrice = (float) $variantData['price'];
            $date = $variantData['date'];
            $oldQuantity = $stock->quantity;

            // Log stock change if quantity changed
            if ($newQuantity !== $oldQuantity) {
                $difference = $newQuantity - $oldQuantity;
                $changeType = $difference > 0 ? 'addition' : 'reduction';

                StockLog::create([
                    'product_id' => $stock->product_id,
                    'product_stock_id' => $stock->id,
                    'quantity' => abs($difference),
                    'change_type' => $changeType,
                    'note' => $note,
                    'created_by' => auth()->id(),
                    'created_at' => $date,
                ]);

                $stock->update(['quantity' => $newQuantity]);
            }

            // Track price change if different
            $product = $stock->product;
            if (abs($newPrice - (float) $product->price) > 0.01) {
                PriceHistory::create([
                    'product_id' => $product->id,
                    'product_stock_id' => $stock->id,
                    'old_price' => $product->price,
                    'new_price' => $newPrice,
                    'changed_by' => auth()->id(),
                    'note' => $note,
                    'created_at' => $date,
                ]);

                // Update product price
                $product->update([
                    'base_price' => $newPrice,
                    'sale_price' => null,
                ]);
            }

            $updatedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.variants_updated_successfully', ['count' => $updatedCount]),
        ]);
    }

    public function createStock(Request $request)
    {
        // Validate unique SKU separately to handle the logic better
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'color_id' => 'nullable|exists:colors,id',
            'attribute_value_id' => 'nullable|exists:attribute_values,id',
            'sku' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $product = Product::find($request->input('product_id'));
        if (! $product) {
            return response()->json(['success' => false, 'message' => __('messages.product_not_found')], 404);
        }

        $sku = $request->input('sku');
        $attributeValueId = $request->input('attribute_value_id');
        $newQuantity = (int) $request->input('quantity');
        $newPrice = (float) $request->input('price');
        $date = $request->input('date');
        $note = $request->input('note');

        // Check if stock with this SKU already exists
        $existingStock = ProductStock::where('sku', $sku)->first();

        if ($existingStock) {
            // SKU already exists - return validation error for duplicate
            return response()->json([
                'success' => false,
                'message' => __('messages.sku_already_exists'),
                'errors' => ['sku' => [__('messages.sku_already_exists')]],
            ], 422);
        }

        // Create new stock entry
        $stock = ProductStock::create([
            'product_id' => $request->input('product_id'),
            'color_id' => $request->input('color_id'),
            'attribute_value_id' => $attributeValueId,
            'sku' => $sku,
            'quantity' => $newQuantity,
            'price' => $newPrice,
        ]);

        // Log stock addition
        StockLog::create([
            'product_id' => $stock->product_id,
            'product_stock_id' => $stock->id,
            'quantity' => $newQuantity,
            'change_type' => 'addition',
            'note' => $note,
            'created_by' => auth()->id(),
            'created_at' => $date,
        ]);

        // Track initial price if different from product price
        if (abs($newPrice - (float) $product->price) > 0.01) {
            PriceHistory::create([
                'product_id' => $product->id,
                'product_stock_id' => $stock->id,
                'old_price' => (float) $product->price,
                'new_price' => $newPrice,
                'changed_by' => auth()->id(),
                'note' => $note,
                'created_at' => $date,
            ]);

            $product->update([
                'base_price' => $newPrice,
                'sale_price' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.stock_added_successfully'),
        ]);
    }
}
