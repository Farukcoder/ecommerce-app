<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'brand_id',
        'name',
        'slug',
        'sku',
        'thumbnail',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
        'base_price',
        'sale_price',
        'discount_type',
        'discount_value',
        'featured',
        'status'
    ];

    protected $casts = [
        'featured' => 'boolean',
        'base_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
    ];

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_color');
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value');
    }
}
