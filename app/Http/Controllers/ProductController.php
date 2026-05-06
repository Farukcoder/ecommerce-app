<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'categories']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
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
            'discount_type'     => 'nullable|in:percentage,fixed',
            'discount_value'    => 'nullable|numeric|min:0',
            'status'            => 'required|in:draft,published,archived',
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
            'status'            => $validated['status'],
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

        // Handle stock variants
        if ($request->has('variants')) {
            foreach ($request->input('variants') as $variant) {
                if (empty($variant['sku'])) {
                    continue; // skip rows without a SKU
                }
                ProductStock::create([
                    'product_id' => $product->id,
                    'sku'        => $variant['sku'],
                    'quantity'   => (int) ($variant['quantity'] ?? 0),
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', "Product \"{$product->name}\" created successfully.");
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $product->load(['brand', 'categories', 'images', 'stocks']);

        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'brands', 'attributes'));
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
            'discount_type'     => 'nullable|in:percentage,fixed',
            'discount_value'    => 'nullable|numeric|min:0',
            'status'            => 'required|in:draft,published,archived',
            'featured'          => 'boolean',
            'brand_id'          => 'nullable|exists:brands,id',
            'categories'        => 'array',
            'categories.*'      => 'exists:categories,id',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:500',
            'thumbnail'         => 'nullable|image|max:2048',
            'gallery.*'         => 'nullable|image|max:2048',
        ]);

        // Handle new thumbnail upload
        $thumbnailPath = $product->thumbnail;
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($product->thumbnail) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')
                ->store('products/thumbnails', 'public');
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
            'status'            => $validated['status'],
            'featured'          => $request->boolean('featured'),
            'brand_id'          => $validated['brand_id'] ?? null,
        ]);

        // Sync categories
        $product->categories()->sync($validated['categories'] ?? []);

        // Handle new gallery images
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

        return redirect()->route('products.index')
            ->with('success', "Product \"{$product->name}\" updated successfully.");
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
}
