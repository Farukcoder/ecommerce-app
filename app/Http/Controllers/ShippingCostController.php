<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Division;
use App\Models\ShippingCost;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class ShippingCostController extends Controller
{
    public function index()
    {
        $systemSetting = SystemSetting::query()->latest('id')->first();
        $divisions = Division::with('districts')->get();
        $selectedDivision = request('division_id') ? Division::findOrFail(request('division_id')) : $divisions->first();

        $districts = $selectedDivision->districts()->with('shippingCost')->get();

        $stats = $this->getShippingStats();

        return view('shipping-costs.index', compact('systemSetting', 'divisions', 'selectedDivision', 'districts', 'stats'));
    }

    public function updateSystemSettings(Request $request)
    {
        $validated = $request->validate([
            'free_shipping_for_everyone' => ['boolean'],
            'default_shipping_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $systemSetting = SystemSetting::query()->latest('id')->first();

        if ($systemSetting) {
            $systemSetting->update($validated);
        } else {
            SystemSetting::create($validated);
        }

        return redirect()->route('shipping-costs.index')->with('success', 'System settings updated successfully.');
    }

    public function updateDistrictCost(Request $request, District $district)
    {
        $validated = $request->validate([
            'cost' => ['required', 'numeric', 'min:0'],
            'is_free' => ['boolean'],
        ]);

        $systemSetting = SystemSetting::query()->latest('id')->first();
        $defaultRate = $systemSetting?->default_shipping_rate ?? 80;

        // If cost equals default rate and not free, delete the shipping cost record (makes it OTHERS)
        if ($validated['cost'] == $defaultRate && ! $validated['is_free']) {
            ShippingCost::query()->where('district_id', $district->id)->delete();
        } else {
            ShippingCost::query()->updateOrCreate(
                ['district_id' => $district->id],
                $validated
            );
        }

        return redirect()->route('shipping-costs.index', ['division_id' => $district->division_id])
            ->with('success', 'Shipping cost updated successfully.');
    }

    public function makeDivisionFree(Division $division)
    {
        $districtIds = $division->districts()->pluck('id');

        foreach ($districtIds as $districtId) {
            ShippingCost::query()->updateOrCreate(
                ['district_id' => $districtId],
                ['cost' => 0, 'is_free' => true]
            );
        }

        return redirect()->route('shipping-costs.index', ['division_id' => $division->id])
            ->with('success', 'Division set to free shipping successfully.');
    }

    protected function getShippingStats(): array
    {
        $totalDistricts = District::query()->count();
        $freeShippingCount = ShippingCost::query()->where('is_free', true)->count();
        $customRateCount = ShippingCost::query()->where('is_free', false)->where('cost', '>', 0)->count();
        $usingDefaultCount = $totalDistricts - $freeShippingCount - $customRateCount;

        return [
            'total' => $totalDistricts,
            'free_shipping' => $freeShippingCount,
            'custom_rate' => $customRateCount,
            'using_default' => max(0, $usingDefaultCount),
        ];
    }
}
