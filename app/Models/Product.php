<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'brand_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'base_price',
        'sale_price',
        'discount_type',
        'discount_value',
        'featured',
        'status'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class);
    }
}
