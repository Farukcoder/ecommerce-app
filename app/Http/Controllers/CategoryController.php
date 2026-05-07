<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (($status = $request->get('status')) !== null && $status !== '') {
            $query->where('status', (bool) $status);
        }

        $categories = $query->latest()->paginate(15)->withQueryString();
        $filters    = $request->only(['search', 'status']);

        return view('categories.index', compact('categories', 'filters'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => 'nullable|string|max:255|unique:categories,slug',
            'image'  => 'nullable|image|max:2048',
            'status' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'name'   => $validated['name'],
            'slug'   => $this->resolveSlug($validated['slug'] ?? null, $validated['name']),
            'image'  => $imagePath,
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('categories.index')
            ->with('success', "Category \"{$validated['name']}\" created successfully.");
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'slug'   => "nullable|string|max:255|unique:categories,slug,{$category->id}",
            'image'  => 'nullable|image|max:2048',
            'status' => 'nullable|boolean',
        ]);

        $imagePath = $category->image;

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = $request->file('image')->store('categories', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = null;
        }

        $category->update([
            'name'   => $validated['name'],
            'slug'   => $this->resolveSlug($validated['slug'] ?? null, $validated['name'], $category->id),
            'image'  => $imagePath,
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('categories.index')
            ->with('success', "Category \"{$category->name}\" updated successfully.");
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $name = $category->name;
        $category->products()->detach(); // remove pivot links before deletion
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "Category \"{$name}\" deleted successfully.");
    }

    /**
     * Auto-generate a unique slug if none was provided.
     */
    protected function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = $slug ? Str::slug($slug) : Str::slug($name);
        $candidate = $base ?: 'category';
        $i = 1;

        while (
            Category::where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = "{$base}-" . (++$i);
        }

        return $candidate;
    }
}
