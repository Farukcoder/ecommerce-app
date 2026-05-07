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
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'base_price' => (float) $this->base_price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value ? (float) $this->discount_value : null,
            'featured' => (bool) $this->featured,
            'status' => $this->status,
            'brand' => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
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
            'total_stock' => $this->whenLoaded('stocks', fn () => $this->stocks->sum('quantity')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
