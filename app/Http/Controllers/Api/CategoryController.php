<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Return active categories for customers.
     */
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'image'])
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image
                        ? asset('storage/' . $category->image)
                        : null,
                ];
            });

        return response()->json([
            'data' => $categories,
        ]);
    }
}
