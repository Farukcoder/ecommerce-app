<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->with(['division', 'district', 'upazila', 'union'])
            ->get();

        return response()->json([
            'data' => $addresses,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'upazila_id' => ['nullable', 'integer', 'exists:upazilas,id'],
            'union_id' => ['nullable', 'integer', 'exists:unions,id'],
            'zip' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $isFirst = $user->addresses()->count() === 0;
        $isDefault = $isFirst || ($validated['is_default'] ?? false);

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create(array_merge($validated, [
            'is_default' => $isDefault,
        ]));

        return response()->json([
            'message' => 'Address created successfully.',
            'data' => $address->load(['division', 'district', 'upazila', 'union']),
        ], 201);
    }

    public function update(Request $request, CustomerAddress $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'upazila_id' => ['nullable', 'integer', 'exists:upazilas,id'],
            'union_id' => ['nullable', 'integer', 'exists:unions,id'],
            'zip' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $isDefault = $validated['is_default'] ?? false;

        // If this is the user's only address, it must be default.
        if ($user->addresses()->count() === 1) {
            $isDefault = true;
        }

        if ($isDefault && !$address->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address->update(array_merge($validated, [
            'is_default' => $isDefault,
        ]));

        return response()->json([
            'message' => 'Address updated successfully.',
            'data' => $address->load(['division', 'district', 'upazila', 'union']),
        ]);
    }

    public function destroy(Request $request, CustomerAddress $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $user = $request->user();
        $wasDefault = $address->is_default;

        $address->delete();

        // If we deleted the default address, make another one default if available.
        if ($wasDefault) {
            $nextAddress = $user->addresses()->first();
            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        return response()->json([
            'message' => 'Address deleted successfully.',
        ]);
    }

    public function setDefault(Request $request, CustomerAddress $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $user = $request->user();
        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'message' => 'Default address set successfully.',
            'data' => $address->load(['division', 'district', 'upazila', 'union']),
        ]);
    }
}
