<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of brands.
     */
    public function index(Request $request)
    {
        $query = Brand::withCount('products');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (($status = $request->get('status')) !== null && $status !== '') {
            $query->where('status', (bool) $status);
        }

        $query->orderBy('id', 'asc');

        $brands  = $query->latest()->paginate(15)->withQueryString();
        $filters = $request->only(['search', 'status']);

        return view('brands.index', compact('brands', 'filters'));
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create()
    {
        return view('brands.create');
    }

    /**
     * Store a newly created brand.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'nullable|string|max:255|unique:brands,slug',
            'logo'   => 'nullable|image|max:2048',
            'status' => 'nullable|boolean',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands', 'public');
        }

        Brand::create([
            'name'   => $validated['name'],
            'slug'   => $this->resolveSlug($validated['slug'] ?? null, $validated['name']),
            'logo'   => $logoPath,
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('brands.index')
            ->with('success', "Brand \"{$validated['name']}\" created successfully.");
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    /**
     * Update the specified brand.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => "nullable|string|max:255|unique:brands,slug,{$brand->id}",
            'logo'   => 'nullable|image|max:2048',
            'status' => 'nullable|boolean',
        ]);

        $logoPath = $brand->logo;

        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $logoPath = $request->file('logo')->store('brands', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $logoPath = null;
        }

        $brand->update([
            'name'   => $validated['name'],
            'slug'   => $this->resolveSlug($validated['slug'] ?? null, $validated['name'], $brand->id),
            'logo'   => $logoPath,
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('brands.index')
            ->with('success', "Brand \"{$brand->name}\" updated successfully.");
    }

    /**
     * Remove the specified brand.
     */
    public function destroy(Brand $brand)
    {
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $name = $brand->name;
        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', "Brand \"{$name}\" deleted successfully.");
    }

    /**
     * Auto-generate a unique slug if none was provided.
     */
    protected function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = $slug ? Str::slug($slug) : Str::slug($name);
        $candidate = $base ?: 'brand';
        $i = 1;

        while (
            Brand::where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = "{$base}-" . (++$i);
        }

        return $candidate;
    }
}
