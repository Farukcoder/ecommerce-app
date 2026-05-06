<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = ['Electronics', 'Clothing', 'Footwear', 'Accessories', 'Home & Garden'];
        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat)],
                ['name' => $cat, 'status' => true]
            );
        }

        // Brands
        $brands = ['Apple', 'Samsung', 'Nike', 'Adidas', 'Sony'];
        foreach ($brands as $brand) {
            Brand::firstOrCreate(
                ['slug' => Str::slug($brand)],
                ['name' => $brand, 'status' => true]
            );
        }

        // Attributes
        $attributes = [
            'Color' => ['Red', 'Blue', 'Green', 'Black', 'White'],
            'Size'  => ['S', 'M', 'L', 'XL'],
            'Material' => ['Cotton', 'Leather', 'Plastic', 'Metal']
        ];

        foreach ($attributes as $attrName => $values) {
            $attribute = Attribute::firstOrCreate(['name' => $attrName]);
            foreach ($values as $value) {
                $attribute->values()->firstOrCreate(['value' => $value]);
            }
        }
    }
}
