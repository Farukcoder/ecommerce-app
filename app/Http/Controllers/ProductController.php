<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductController extends Controller
{
    private const ALLOWED_STATUSES = ['draft', 'published', 'archived'];

    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'categories', 'images']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $this->normalizeStatus($request->get('status'))) {
            $query->where('status', $status);
        }

        // Filter by category
        if ($categoryId = $request->get('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId));
        }

        // Filter by brand
        if ($brandId = $request->get('brand')) {
            $query->where('brand_id', $brandId);
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();
        $filters    = $request->only(['search', 'status', 'category', 'brand']);
        $filters['status'] = $this->normalizeStatus($filters['status'] ?? null) ?? ($filters['status'] ?? '');

        return view('products.index', compact('products', 'categories', 'brands', 'filters'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('name')->get();

        return view('products.create', compact('categories', 'brands', 'attributes'));
    }

    /**
     * Show the bulk import form.
     */
    public function importForm()
    {
        return view('products.import');
    }

    /**
     * Download a CSV sample for product import.
     */
    public function downloadImportSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product-import-sample.csv"',
        ];

        $columns = [
            'name',
            'slug',
            'sku',
            'short_description',
            'description',
            'base_price',
            'sale_price',
            'discount_type',
            'discount_value',
            'status',
            'featured',
            'brand_id',
            'category_ids',
            'meta_title',
            'meta_description',
            'thumbnail_path',
            'gallery_paths',
            'stock_sku',
            'stock_quantity',
            'stock_price',
            'attribute_id',
            'attribute_value_id',
            'attribute_name',
            'attribute_value',
        ];

        $rows = [
            [
                'Sample Product',
                'sample-product',
                'SKU-1001',
                'Short description here',
                'Full product description goes here',
                '1200',
                '999',
                'fixed',
                '200',
                'published',
                'yes',
                '1',
                '2,3',
                'Meta title sample',
                'Meta description sample',
                'images/sample-thumb.jpg',
                'images/sample-1.jpg,images/sample-2.jpg',
                'SKU-1001-S',
                '10',
                '1200',
                '',
                '',
                'Size',
                'S',
            ],
        ];

        $callback = function () use ($columns, $rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $columns);
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle bulk product import from spreadsheet.
     */
    public function importStore(Request $request)
    {

        $request->validate([
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $extension = strtolower((string) $value->getClientOriginalExtension());
                    if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
                        $fail('The '.$attribute.' must be a file of type: xlsx, xls, csv.');
                    }
                },
                'mimetypes:text/csv,text/plain,application/csv,application/excel,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream',
            ],
            'base_path' => ['nullable', 'string', 'max:500'],
        ]);

        $basePath = trim((string) $request->input('base_path'));
        if ($basePath === '') {
            $basePath = storage_path('app/imports');
        }

        $rows = $this->readSpreadsheetRows($request->file('file')->getRealPath());

        if (empty($rows)) {
            return back()->withErrors(['file' => 'The spreadsheet is empty.']);
        }

        $errors = [];
        $preparedProducts = [];

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2;
            $data = $this->normalizeImportRow($row);

            $validator = validator($data, [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
                'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
                'short_description' => ['nullable', 'string', 'max:500'],
                'description' => ['nullable', 'string'],
                'base_price' => ['required', 'numeric', 'min:0'],
                'sale_price' => ['nullable', 'numeric', 'min:0'],
                'discount_type' => ['nullable', 'in:fixed,percentage'],
                'discount_value' => ['nullable', 'numeric', 'min:0'],
                'status' => ['nullable', 'in:draft,published,archived,Draft,Published,Archived'],
                'featured' => ['nullable'],
                'brand_id' => ['nullable', 'exists:brands,id'],
                'category_ids' => ['nullable', 'string'],
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string', 'max:500'],
                'thumbnail_path' => ['nullable', 'string'],
                'gallery_paths' => ['nullable', 'string'],
                'stock_sku' => ['nullable', 'string', 'max:100'],
                'stock_quantity' => ['nullable', 'numeric', 'min:0'],
                'stock_price' => ['nullable', 'numeric', 'min:0'],
                'attribute_id' => ['nullable', 'exists:attributes,id'],
                'attribute_value_id' => ['nullable', 'exists:attribute_values,id'],
                'attribute_name' => ['nullable', 'string', 'max:255'],
                'attribute_value' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$rowNumber}: " . implode(' ', $validator->errors()->all());
                continue;
            }

            if (! empty($data['attribute_value_id']) && ! empty($data['attribute_id'])) {
                $attributeValue = AttributeValue::find((int) $data['attribute_value_id']);
                if ($attributeValue && $attributeValue->attribute_id !== (int) $data['attribute_id']) {
                    $errors[] = "Row {$rowNumber}: Attribute value ID does not match attribute ID.";
                    continue;
                }
            }

            if (empty($data['attribute_value_id']) && (trim((string) ($data['attribute_name'] ?? '')) !== '' || trim((string) ($data['attribute_value'] ?? '')) !== '')) {
                if (trim((string) ($data['attribute_name'] ?? '')) === '') {
                    $errors[] = "Row {$rowNumber}: attribute_name is required when attribute_value is provided.";
                    continue;
                }

                if (trim((string) ($data['attribute_value'] ?? '')) === '') {
                    $errors[] = "Row {$rowNumber}: attribute_value is required when attribute_name is provided.";
                    continue;
                }
            }

            $categoryIds = $this->parseCategoryIds($data['category_ids'] ?? '');
            if (! empty($categoryIds)) {
                $found = Category::whereIn('id', $categoryIds)->pluck('id')->all();
                $missing = array_diff($categoryIds, $found);
                if (! empty($missing)) {
                    $errors[] = "Row {$rowNumber}: Unknown category IDs - " . implode(', ', $missing);
                    continue;
                }
            }

            $thumbnailPath = trim((string) ($data['thumbnail_path'] ?? ''));
            if ($thumbnailPath !== '' && ! $this->importImageExists($thumbnailPath, $basePath)) {
                $errors[] = "Row {$rowNumber}: Thumbnail path not found - {$thumbnailPath}";
                continue;
            }

            $galleryPaths = $this->splitList($data['gallery_paths'] ?? '');
            $missingGallery = array_filter($galleryPaths, fn ($path) => ! $this->importImageExists($path, $basePath));
            if (! empty($missingGallery)) {
                $errors[] = "Row {$rowNumber}: Gallery paths not found - " . implode(', ', $missingGallery);
                continue;
            }

            $productKey = $this->normalizeImportProductKey($data);
            if ($productKey === '') {
                $errors[] = "Row {$rowNumber}: Each row must include a product name, SKU, or slug.";
                continue;
            }

            if (! isset($preparedProducts[$productKey])) {
                $preparedProducts[$productKey] = [
                    'data' => [
                        'name' => $data['name'],
                        'slug' => $data['slug'] ?: '',
                        'sku' => $data['sku'] ?: '',
                        'short_description' => $data['short_description'] ?? null,
                        'description' => $data['description'] ?? null,
                        'base_price' => $data['base_price'],
                        'sale_price' => $data['sale_price'] ?? null,
                        'discount_type' => $data['discount_type'] ?? null,
                        'discount_value' => $data['discount_value'] ?? null,
                        'status' => $this->normalizeStatus($data['status'] ?? 'draft') ?? 'draft',
                        'featured' => $this->normalizeBoolean($data['featured'] ?? false),
                        'brand_id' => $data['brand_id'] ?? null,
                        'meta_title' => $data['meta_title'] ?? null,
                        'meta_description' => $data['meta_description'] ?? null,
                    ],
                    'category_ids' => $categoryIds,
                    'thumbnail_path' => $thumbnailPath,
                    'gallery_paths' => $galleryPaths,
                    'variant_rows' => [],
                ];
            } else {
                $preparedProducts[$productKey]['category_ids'] = array_values(array_unique(array_merge(
                    $preparedProducts[$productKey]['category_ids'],
                    $categoryIds
                )));

                if ($preparedProducts[$productKey]['thumbnail_path'] === '' && $thumbnailPath !== '') {
                    $preparedProducts[$productKey]['thumbnail_path'] = $thumbnailPath;
                }

                $preparedProducts[$productKey]['gallery_paths'] = array_values(array_unique(array_merge(
                    $preparedProducts[$productKey]['gallery_paths'],
                    $galleryPaths
                )));
            }

            $preparedProducts[$productKey]['variant_rows'][] = [
                'stock_sku' => trim((string) ($data['stock_sku'] ?? '')),
                'stock_quantity' => $data['stock_quantity'] !== '' ? $data['stock_quantity'] : null,
                'stock_price' => $data['stock_price'] !== '' ? $data['stock_price'] : null,
                'attribute_id' => $data['attribute_id'] ?? null,
                'attribute_value_id' => $data['attribute_value_id'] ?? null,
                'attribute_name' => trim((string) ($data['attribute_name'] ?? '')),
                'attribute_value' => trim((string) ($data['attribute_value'] ?? '')),
            ];
        }

        if (! empty($errors)) {
            return back()->with('import_errors', $errors)->withInput();
        }

        foreach ($preparedProducts as $key => $preparedProduct) {
            $slug = trim((string) $preparedProduct['data']['slug']);
            $sku = trim((string) $preparedProduct['data']['sku']);

            if ($slug !== '' && Product::where('slug', $slug)->exists()) {
                $errors[] = "Product slug already exists: {$slug}";
            }

            if ($sku !== '' && Product::where('sku', $sku)->exists()) {
                $errors[] = "Product SKU already exists: {$sku}";
            }

            if (! empty($errors)) {
                return back()->with('import_errors', $errors)->withInput();
            }

            $preparedProducts[$key]['data']['slug'] = $slug ?: $this->generateUniqueSlug($preparedProduct['data']['name']);
        }

        DB::transaction(function () use ($preparedProducts, $basePath) {
            foreach ($preparedProducts as $preparedProduct) {
                $data = $preparedProduct['data'];

                $thumbnailStoredPath = null;
                if ($preparedProduct['thumbnail_path'] !== '') {
                    $thumbnailStoredPath = $this->storeImportImage($preparedProduct['thumbnail_path'], $basePath, 'products/thumbnails');
                }

                $product = Product::create([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'sku' => $data['sku'] ?: null,
                    'thumbnail' => $thumbnailStoredPath,
                    'short_description' => $data['short_description'] ?? null,
                    'description' => $data['description'] ?? null,
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'base_price' => (float) $data['base_price'],
                    'sale_price' => $data['sale_price'] !== '' ? (float) $data['sale_price'] : null,
                    'discount_type' => $data['discount_type'] ?? null,
                    'discount_value' => $data['discount_value'] !== '' ? (float) $data['discount_value'] : null,
                    'status' => $data['status'],
                    'featured' => $data['featured'],
                    'brand_id' => $data['brand_id'] ?? null,
                ]);

                if (! empty($preparedProduct['category_ids'])) {
                    $product->categories()->sync($preparedProduct['category_ids']);
                }

                foreach ($preparedProduct['gallery_paths'] as $index => $path) {
                    $storedPath = $this->storeImportImage($path, $basePath, 'products/gallery');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $storedPath,
                        'is_primary' => $index === 0,
                    ]);
                }

                $attributeValueIds = [];
                foreach ($preparedProduct['variant_rows'] as $index => $variant) {
                    $quantity = $variant['stock_quantity'] !== null ? max(0, (int) $variant['stock_quantity']) : 0;
                    $price = $variant['stock_price'] !== null ? (float) $variant['stock_price'] : (float) $product->base_price;
                    $sku = $variant['stock_sku'] ?: ($product->sku ? "{$product->sku}-V" . ($index + 1) : "PRD-{$product->id}-V" . ($index + 1));
                    $attributeValueId = $this->resolveImportAttributeValueId($variant);

                    if ($attributeValueId !== null) {
                        $attributeValueIds[] = $attributeValueId;
                    }

                    ProductStock::create([
                        'product_id' => $product->id,
                        'attribute_value_id' => $attributeValueId,
                        'sku' => $sku,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);
                }

                if (! empty($attributeValueIds)) {
                    $product->attributeValues()->sync(array_values(array_unique($attributeValueIds)));
                }
            }
        });

        return redirect()->route('products.index')
            ->with('success', 'Products imported successfully.');
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => 'required|string|max:255|unique:products',
            'sku'               => 'nullable|string|max:100|unique:products',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'base_price'        => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'discount_type'     => 'nullable|in:fixed,percentage',
            'discount_value'    => 'nullable|numeric|min:0',
            'status'            => ['required', 'string', Rule::in(['draft', 'published', 'archived', 'Draft', 'Published', 'Archived'])],
            'featured'          => 'boolean',
            'brand_id'          => 'nullable|exists:brands,id',
            'categories'        => 'array',
            'categories.*'      => 'exists:categories,id',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:500',
            'thumbnail'         => 'nullable|image|max:2048',
            'gallery.*'         => 'nullable|image|max:2048',
        ]);

        // Handle thumbnail upload
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')
                ->store('products/thumbnails', 'public');
        }

        $product = Product::create([
            'name'              => $validated['name'],
            'slug'              => $validated['slug'],
            'sku'               => $validated['sku'] ?? null,
            'thumbnail'         => $thumbnailPath,
            'short_description' => $validated['short_description'] ?? null,
            'description'       => $validated['description'] ?? null,
            'meta_title'        => $validated['meta_title'] ?? null,
            'meta_description'  => $validated['meta_description'] ?? null,
            'base_price'        => $validated['base_price'],
            'sale_price'        => $validated['sale_price'] ?? null,
            'discount_type'     => $validated['discount_type'] ?? null,
            'discount_value'    => $validated['discount_value'] ?? null,
            'status'            => $this->normalizeStatus($validated['status']),
            'featured'          => $request->boolean('featured'),
            'brand_id'          => $validated['brand_id'] ?? null,
        ]);

        // Sync categories
        if (!empty($validated['categories'])) {
            $product->categories()->sync($validated['categories']);
        }

        // Handle gallery images
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $i => $file) {
                $path = $file->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                    'is_primary' => $i === 0,
                ]);
            }
        }

        $this->syncStocks($product, $request->input('variants', []));
        $this->syncAttributeValues($product, $request->input('attributes_payload'));

        return redirect()->route('products.index')
            ->with('success', "Product \"{$product->name}\" created successfully.");
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => "required|string|max:255|unique:products,slug,{$product->id}",
            'sku'               => "nullable|string|max:100|unique:products,sku,{$product->id}",
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'base_price'        => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'discount_type'     => 'nullable|in:fixed,percentage',
            'discount_value'    => 'nullable|numeric|min:0',
            'status'            => ['required', 'string', Rule::in(['draft', 'published', 'archived', 'Draft', 'Published', 'Archived'])],
            'featured'          => 'boolean',
            'brand_id'          => 'nullable|exists:brands,id',
            'categories'        => 'array',
            'categories.*'      => 'exists:categories,id',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:500',
            'thumbnail'         => 'nullable|image|max:2048',
            'gallery.*'         => 'nullable|image|max:2048',
        ]);

        $thumbnailPath = $product->thumbnail;
        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }

            $thumbnailPath = $request->file('thumbnail')->store('products/thumbnails', 'public');
        }

        $product->update([
            'name'              => $validated['name'],
            'slug'              => $validated['slug'],
            'sku'               => $validated['sku'] ?? null,
            'thumbnail'         => $thumbnailPath,
            'short_description' => $validated['short_description'] ?? null,
            'description'       => $validated['description'] ?? null,
            'meta_title'        => $validated['meta_title'] ?? null,
            'meta_description'  => $validated['meta_description'] ?? null,
            'base_price'        => $validated['base_price'],
            'sale_price'        => $validated['sale_price'] ?? null,
            'discount_type'     => $validated['discount_type'] ?? null,
            'discount_value'    => $validated['discount_value'] ?? null,
            'status'            => $this->normalizeStatus($validated['status']),
            'featured'          => $request->boolean('featured'),
            'brand_id'          => $validated['brand_id'] ?? null,
        ]);

        $product->categories()->sync($validated['categories'] ?? []);

        if ($ids = $request->input('delete_images', [])) {
            $toDelete = ProductImage::where('product_id', $product->id)
                ->whereIn('id', $ids)
                ->get();

            foreach ($toDelete as $img) {
                Storage::disk('public')->delete($img->image);
                $img->delete();
            }
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                    'is_primary' => false,
                ]);
            }
        }

        $this->syncStocks($product, $request->input('variants', []));
        $this->syncAttributeValues($product, $request->input('attributes_payload'));

        return redirect()->route('products.index')
            ->with('success', "Product \"{$product->name}\" updated successfully.");
    }

    protected function syncStocks(Product $product, array $variants): void
    {
        $rows = collect($variants)->filter(
            fn ($v) => isset($v['sku']) || isset($v['quantity'])
        )->values();

        if ($rows->isEmpty()) {
            ProductStock::updateOrCreate(
                ['product_id' => $product->id, 'sku' => $product->sku ?: "PRD-{$product->id}"],
                ['quantity' => 0, 'price' => (float) $product->base_price]
            );
            return;
        }

        $keepIds = [];

        foreach ($rows as $i => $variant) {
            $sku = trim((string) ($variant['sku'] ?? '')) ?:
                ($product->sku ? "{$product->sku}-V" . ($i + 1) : "PRD-{$product->id}-V" . ($i + 1));

            $stock = ProductStock::updateOrCreate(
                ['product_id' => $product->id, 'sku' => $sku],
                [
                    'quantity' => max(0, (int) ($variant['quantity'] ?? 0)),
                    'price' => isset($variant['price']) ? (float) $variant['price'] : (float) $product->base_price,
                    'color_id' => $variant['color_id'] ?? null,
                    'attribute_value_id' => $variant['attribute_value_id'] ?? null,
                ]
            );

            $keepIds[] = $stock->id;
        }

        // Remove stock rows that are no longer in the submitted set
        ProductStock::where('product_id', $product->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $product->load(['brand', 'categories', 'images', 'stocks.attributeValue.attribute', 'attributeValues.attribute']);

        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('name')->get();

        $existingAttributeRows = $product->attributeValues
            ->groupBy('attribute_id')
            ->map(function ($group) {
                return [
                    'attrId' => (int) $group->first()->attribute_id,
                    'values' => $group->pluck('value')->values()->all(),
                ];
            })
            ->values()
            ->all();

        $existingVariants = $product->stocks
            ->map(function ($stock, $index) {
                $label = "Variant " . ($index + 1);

                if ($stock->attributeValue && $stock->attributeValue->attribute) {
                    $label = $stock->attributeValue->attribute->name . ': ' . $stock->attributeValue->value;
                }

                return [
                    'label' => $label,
                    'sku' => $stock->sku,
                    'quantity' => (int) $stock->quantity,
                ];
            })
            ->values()
            ->all();

        return view('products.edit', compact('product', 'categories', 'brands', 'attributes', 'existingAttributeRows', 'existingVariants'));
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load([
            'brand',
            'categories',
            'images',
            'stocks.attributeValue.attribute',
            'attributeValues.attribute',
        ]);

        return view('products.show', compact('product'));
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        // Delete thumbnail from storage
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }

        // Delete gallery images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image);
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Product \"{$name}\" deleted successfully.");
    }

    private function normalizeStatus(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        $normalized = strtolower(trim($status));

        return in_array($normalized, self::ALLOWED_STATUSES, true) ? $normalized : null;
    }

    private function normalizeImportRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$key] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    private function readSpreadsheetRows(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, true);

        if (count($rawRows) < 2) {
            return [];
        }

        $headerRow = array_shift($rawRows);
        $headerMap = [];
        $allowedHeaders = [
            'name',
            'slug',
            'sku',
            'short_description',
            'description',
            'base_price',
            'sale_price',
            'discount_type',
            'discount_value',
            'status',
            'featured',
            'brand_id',
            'category_ids',
            'meta_title',
            'meta_description',
            'thumbnail_path',
            'gallery_paths',
            'stock_sku',
            'stock_quantity',
            'stock_price',
            'attribute_id',
            'attribute_value_id',
            'attribute_name',
            'attribute_value',
        ];

        foreach ($headerRow as $col => $header) {
            $key = strtolower(trim((string) $header));
            if (in_array($key, $allowedHeaders, true)) {
                $headerMap[$col] = $key;
            }
        }

        if (empty($headerMap)) {
            return [];
        }

        $rows = [];
        foreach ($rawRows as $row) {
            $mapped = [];
            foreach ($headerMap as $col => $key) {
                $mapped[$key] = $row[$col] ?? null;
            }

            if (count(array_filter($mapped, fn ($value) => $value !== null && $value !== '')) === 0) {
                continue;
            }

            $rows[] = $mapped;
        }

        return $rows;
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'y'], true);
    }

    private function parseCategoryIds(string $value): array
    {
        return array_values(array_filter(array_map(
            fn ($item) => is_numeric($item) ? (int) $item : null,
            $this->splitList($value)
        )));
    }

    private function splitList(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function importImageExists(string $path, string $basePath): bool
    {
        $resolved = $this->resolveImportPath($path, $basePath);

        return $resolved !== '' && File::exists($resolved);
    }

    private function storeImportImage(string $path, string $basePath, string $directory): string
    {
        $resolved = $this->resolveImportPath($path, $basePath);

        $extension = pathinfo($resolved, PATHINFO_EXTENSION);
        $filename = Str::uuid()->toString() . ($extension ? '.' . $extension : '');
        $targetDir = storage_path('app/public/' . $directory);

        File::ensureDirectoryExists($targetDir);

        File::copy($resolved, $targetDir . DIRECTORY_SEPARATOR . $filename);

        return $directory . '/' . $filename;
    }

    private function resolveImportPath(string $path, string $basePath): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        $normalizedBasePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $relativePath = ltrim($path, '/\\');

        $candidates = [
            $normalizedBasePath . DIRECTORY_SEPARATOR . $relativePath,
        ];

        if (str_contains($relativePath, DIRECTORY_SEPARATOR) || str_contains($relativePath, '/')) {
            $candidates[] = $normalizedBasePath . DIRECTORY_SEPARATOR . basename($relativePath);
        }

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return $candidates[0] ?? '';
    }

    private function resolveImportAttributeValueId(array $variant): ?int
    {
        if (! empty($variant['attribute_value_id'])) {
            return (int) $variant['attribute_value_id'];
        }

        if (trim((string) ($variant['attribute_value'] ?? '')) === '') {
            return null;
        }

        $attributeName = trim((string) ($variant['attribute_name'] ?? ''));
        $attribute = null;

        if (! empty($variant['attribute_id'])) {
            $attribute = Attribute::find((int) $variant['attribute_id']);
        }

        if (! $attribute) {
            $attribute = Attribute::firstOrCreate([
                'name' => $attributeName,
            ]);
        }

        $attributeValue = AttributeValue::firstOrCreate([
            'attribute_id' => $attribute->id,
            'value' => trim((string) $variant['attribute_value']),
        ]);

        return $attributeValue->id;
    }

    private function normalizeImportProductKey(array $data): string
    {
        $key = trim((string) ($data['slug'] ?? '')) ?: trim((string) ($data['sku'] ?? ''));

        if ($key === '') {
            return strtolower(trim((string) ($data['name'] ?? '')));
        }

        return strtolower($key);
    }

    private function isAbsolutePath(string $path): bool
    {
        return (bool) preg_match('/^[a-zA-Z]:\\\\|^\//', $path);
    }

    private function syncAttributeValues(Product $product, mixed $attributesPayload): void
    {
        if (!is_string($attributesPayload) || trim($attributesPayload) === '') {
            return;
        }

        $rows = json_decode($attributesPayload, true);

        if (!is_array($rows)) {
            return;
        }

        $attributeValueIds = collect($rows)
            ->filter(fn ($row) => is_array($row) && !empty($row['attrId']) && is_array($row['values'] ?? null))
            ->flatMap(function ($row) {
                $attributeId = (int) $row['attrId'];

                return collect($row['values'])
                    ->map(fn ($value) => is_string($value) ? trim($value) : '')
                    ->filter()
                    ->map(function ($value) use ($attributeId) {
                        return AttributeValue::firstOrCreate([
                            'attribute_id' => $attributeId,
                            'value' => $value,
                        ])->id;
                    });
            })
            ->unique()
            ->values()
            ->all();

        $product->attributeValues()->sync($attributeValueIds);
    }
}
