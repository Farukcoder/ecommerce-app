<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function divisions(): JsonResponse
    {
        $divisions = Division::orderBy('name')->get();
        return response()->json([
            'data' => $divisions,
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $query = District::query();
        if ($request->has('division_id')) {
            $query->where('division_id', $request->division_id);
        }
        $districts = $query->orderBy('name')->get();
        return response()->json([
            'data' => $districts,
        ]);
    }

    public function upazilas(Request $request): JsonResponse
    {
        $query = Upazila::query();
        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }
        $upazilas = $query->orderBy('name')->get();
        return response()->json([
            'data' => $upazilas,
        ]);
    }

    public function unions(Request $request): JsonResponse
    {
        $query = Union::query();
        if ($request->has('upazila_id')) {
            $query->where('upazila_id', $request->upazila_id);
        }
        $unions = $query->orderBy('name')->get();
        return response()->json([
            'data' => $unions,
        ]);
    }
}
