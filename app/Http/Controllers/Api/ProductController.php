<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Return published products for the storefront.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with([
                'brand',
                'categories',
                'images',
                'stocks.color',
                'attributeValues.attribute',
            ])
            ->whereRaw('LOWER(status) = ?', ['published']);

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // dd($query);

        if ($category = trim((string) $request->string('category'))) {
            $query->whereHas('categories', function ($builder) use ($category) {
                if (is_numeric($category)) {
                    $builder->where('categories.id', $category);

                    return;
                }

                $builder->where('slug', $category);
            });
        }

        if ($brand = trim((string) $request->string('brand'))) {
            $query->where(function ($builder) use ($brand) {
                if (is_numeric($brand)) {
                    $builder->where('brand_id', $brand);

                    return;
                }

                $builder->whereHas('brand', fn ($brandQuery) => $brandQuery->where('slug', $brand));
            });
        }

        if ($request->filled('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        if ($badge = trim((string) $request->string('badge'))) {
            match ($badge) {
                'sale' => $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'base_price'),
                'new' => $query->where('created_at', '>=', now()->subDays(30)),
                'bestseller' => $query->where('featured', true),
                default => null,
            };
        }

        $perPage = max(1, min((int) $request->integer('per_page', 12), 100));

        return ProductResource::collection(
            $query->latest()->paginate($perPage)->withQueryString()
        )->response();
    }

    /**
     * Show a single published product.
     */
    public function show(Product $product): ProductResource
    {
        abort_if(strtolower($product->status) !== 'published', 404);

        return new ProductResource(
            $product->load([
                'brand',
                'categories',
                'images',
                'stocks.color',
                'attributeValues.attribute',
            ])
        );
    }
}
