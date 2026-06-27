<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Collection;

class ProductService
{
    /**
     * Get product with all relationships
     */
    public function getProduct($id)
    {
        return Product::with([
            'brand',
            'categories',
            'images',
            'colors',
            'stocks',
            'attributeValues',
        ])->findOrFail($id);
    }

    /**
     * Get all products with filters
     */
    public function getAllProducts(array $filters = [])
    {
        $query = Product::with(['brand', 'categories']);

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->whereHas('categories', fn ($q) =>
                $q->where('categories.id', $filters['category_id'])
            );
        }

        // Filter by brand
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // Filter by featured
        if (isset($filters['featured'])) {
            $query->where('featured', $filters['featured']);
        }

        // Price range
        if (!empty($filters['price_min'])) {
            $query->where('base_price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('base_price', '<=', $filters['price_max']);
        }

        return $query;
    }

    /**
     * Sync product stocks
     */
    public function syncStocks(Product $product, array $variants): void
    {
        $rows = collect($variants)->filter(
            fn ($v) => isset($v['sku']) || isset($v['quantity'])
        )->values();

        if ($rows->isEmpty()) {
            ProductStock::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sku' => $product->sku ?: "PRD-{$product->id}",
                    'color_id' => null,
                    'attribute_value_id' => null,
                ],
                [
                    'quantity' => 0,
                    'price' => (float) $product->base_price,
                ]
            );
            return;
        }

        $keepIds = [];

        foreach ($rows as $i => $variant) {
            $sku = trim((string) ($variant['sku'] ?? '')) ?:
                ($product->sku ? "{$product->sku}-V" . ($i + 1) : "PRD-{$product->id}-V" . ($i + 1));

            $stock = ProductStock::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sku' => $sku,
                ],
                [
                    'quantity' => max(0, (int) ($variant['quantity'] ?? 0)),
                    'price' => isset($variant['price']) ? (float) $variant['price'] : (float) $product->base_price,
                    'color_id' => $variant['color_id'] ?? null,
                    'attribute_value_id' => $variant['attribute_value_id'] ?? null,
                ]
            );

            $keepIds[] = $stock->id;
        }

        ProductStock::where('product_id', $product->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    /**
     * Get product total stock
     */
    public function getTotalStock($productId): int
    {
        return ProductStock::where('product_id', $productId)->sum('quantity');
    }

    /**
     * Check if product stock is available
     */
    public function isStockAvailable($productId, $quantity = 1): bool
    {
        return $this->getTotalStock($productId) >= $quantity;
    }

    /**
     * Reduce product stock
     */
    public function reduceStock($productId, $quantity): bool
    {
        if (!$this->isStockAvailable($productId, $quantity)) {
            return false;
        }

        $stocks = ProductStock::where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $remaining = $quantity;

        foreach ($stocks as $stock) {
            if ($remaining <= 0) {
                break;
            }

            $reduce = min($stock->quantity, $remaining);
            $stock->reduceStock($reduce);
            $remaining -= $reduce;
        }

        return true;
    }

    /**
     * Increase product stock
     */
    public function increaseStock($productId, $quantity): void
    {
        $stock = ProductStock::where('product_id', $productId)
            ->orderBy('created_at', 'asc')
            ->first();

        if ($stock) {
            $stock->increaseStock($quantity);
        }
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts($threshold = 10)
    {
        return Product::with('stocks')
            ->get()
            ->filter(function ($product) use ($threshold) {
                $total = $product->stocks->sum('quantity');
                return $total > 0 && $total <= $threshold;
            });
    }

    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts()
    {
        return Product::with('stocks')
            ->whereDoesntHave('stocks', fn ($q) => $q->where('quantity', '>', 0))
            ->get();
    }
}
