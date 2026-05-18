<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    /**
     * Return all active brands for customers.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'logo'])
            ->map(function (Brand $brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'logo' => $brand->logo
                        ? asset('storage/' . $brand->logo)
                        : null,
                ];
            });

        return response()->json([
            'data' => $brands,
        ]);
    }
}
