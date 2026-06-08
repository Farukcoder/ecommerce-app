<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $primaryCategory = $this->categories?->first();
        $primaryImage = $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : ($this->images?->first()?->image
                ? asset('storage/' . $this->images->first()->image)
                : 'https://images.unsplash.com/photo-1523275335684-de89df76afd3?w=600&h=800&fit=crop');

        $currentPrice = $this->sale_price && (float) $this->sale_price < (float) $this->base_price
            ? (float) $this->sale_price
            : (float) $this->base_price;

        $originalPrice = $currentPrice < (float) $this->base_price
            ? (float) $this->base_price
            : null;

        return [
            'id' => (string) $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'image' => $primaryImage,
            'thumbnail' => $primaryImage,
            'price' => $currentPrice,
            'originalPrice' => $originalPrice,
            'base_price' => (float) $this->base_price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value ? (float) $this->discount_value : null,
            'category' => $primaryCategory?->slug,
            'categoryName' => $primaryCategory?->name,
            'featured' => (bool) $this->featured,
            'status' => $this->status,
            'badge' => $this->resolveBadge(),
            'rating' => $this->relationLoaded('reviews') || isset($this->reviews_avg_rating)
                ? ($this->reviews_avg_rating ? round((float)$this->reviews_avg_rating, 1) : ($this->reviews->avg('rating') ? round($this->reviews->avg('rating'), 1) : 0))
                : 0,
            'reviews' => $this->relationLoaded('reviews') || isset($this->reviews_count)
                ? (int)($this->reviews_count ?? $this->reviews->count())
                : 0,
            'brand' => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
                'slug' => $this->brand?->slug,
            ],
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ]);
            }),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => asset('storage/' . $img->image),
                    'is_primary' => (bool) $img->is_primary,
                ]);
            }),
            'stocks' => $this->whenLoaded('stocks', function () {
                return $this->stocks->map(fn ($stock) => [
                    'id' => $stock->id,
                    'sku' => $stock->sku,
                    'quantity' => $stock->quantity,
                    'color' => $stock->color ? ['id' => $stock->color->id, 'name' => $stock->color->name] : null,
                ]);
            }),
            'sizes' => $this->whenLoaded('attributeValues', function () {
                return $this->attributeValues
                    ->map(fn ($attributeValue) => $attributeValue->value)
                    ->filter()
                    ->unique()
                    ->values();
            }, []),
            'colors' => $this->whenLoaded('stocks', function () {
                return $this->stocks
                    ->map(fn ($stock) => $stock->color?->name)
                    ->filter()
                    ->unique()
                    ->values();
            }, []),
            'stock' => $this->whenLoaded('stocks', fn () => (int) $this->stocks->sum('quantity'), 0),
            'total_stock' => $this->whenLoaded('stocks', fn () => (int) $this->stocks->sum('quantity'), 0),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function resolveBadge(): ?string
    {
        if ($this->featured) {
            return 'bestseller';
        }

        if ($this->sale_price && (float) $this->sale_price < (float) $this->base_price) {
            return 'sale';
        }

        if ($this->created_at && $this->created_at->gte(now()->subDays(30))) {
            return 'new';
        }

        return null;
    }
}
