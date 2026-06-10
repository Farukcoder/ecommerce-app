<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of locations based on the active tab.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'division');
        $search = $request->get('search');

        $divisionsList = [];
        $districtsList = [];
        $upazilasList = [];

        // Preload standard dropdown filters depending on the tab
        if ($tab === 'district') {
            $divisionsList = Division::orderBy('name')->get();
        } elseif ($tab === 'upazila') {
            $divisionsList = Division::orderBy('name')->get();
            $districtsList = District::with('division')->orderBy('name')->get();
        } elseif ($tab === 'union') {
            $divisionsList = Division::orderBy('name')->get();
            $districtsList = District::with('division')->orderBy('name')->get();
            $upazilasList = Upazila::with('district')->orderBy('name')->get();
        }

        switch ($tab) {
            case 'district':
                $query = District::with('division');
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('bn_name', 'like', "%{$search}%");
                    });
                }
                if ($divisionId = $request->get('division_id')) {
                    $query->where('division_id', $divisionId);
                }
                $items = $query->orderBy('name')->paginate(15)->withQueryString();
                break;

            case 'upazila':
                $query = Upazila::with('district.division');
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('bn_name', 'like', "%{$search}%");
                    });
                }
                if ($districtId = $request->get('district_id')) {
                    $query->where('district_id', $districtId);
                } elseif ($divisionId = $request->get('division_id')) {
                    $query->whereHas('district', function ($q) use ($divisionId) {
                        $q->where('division_id', $divisionId);
                    });
                }
                $items = $query->orderBy('name')->paginate(15)->withQueryString();
                break;

            case 'union':
                $query = Union::with('upazila.district');
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('bn_name', 'like', "%{$search}%");
                    });
                }
                if ($upazilaId = $request->get('upazila_id')) {
                    $query->where('upazila_id', $upazilaId);
                } elseif ($districtId = $request->get('district_id')) {
                    $query->whereHas('upazila', function ($q) use ($districtId) {
                        $q->where('district_id', $districtId);
                    });
                } elseif ($divisionId = $request->get('division_id')) {
                    $query->whereHas('upazila.district', function ($q) use ($divisionId) {
                        $q->where('division_id', $divisionId);
                    });
                }
                $items = $query->orderBy('name')->paginate(15)->withQueryString();
                break;

            case 'division':
            default:
                $query = Division::query();
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('bn_name', 'like', "%{$search}%");
                    });
                }
                $items = $query->orderBy('name')->paginate(15)->withQueryString();
                break;
        }

        $filters = $request->only(['search', 'division_id', 'district_id', 'upazila_id']);

        return view('locations.index', compact(
            'tab',
            'items',
            'filters',
            'divisionsList',
            'districtsList',
            'upazilasList'
        ));
    }

    /**
     * Show the form for creating a new location.
     */
    public function create(Request $request)
    {
        $selectedType = $request->get('type', 'division');
        $divisions = Division::orderBy('name')->get();
        $districts = District::with('division')->orderBy('name')->get();
        $upazilas = Upazila::with('district')->orderBy('name')->get();

        return view('locations.create', compact('selectedType', 'divisions', 'districts', 'upazilas'));
    }

    /**
     * Store a newly created location.
     */
    public function store(Request $request)
    {
        $type = $request->input('type', 'division');

        switch ($type) {
            case 'district':
                $validated = $request->validate([
                    'division_id' => 'required|exists:divisions,id',
                    'name' => 'required|string|max:255',
                    'bn_name' => 'required|string|max:255',
                    'lat' => 'nullable|string|max:255',
                    'long' => 'nullable|string|max:255',
                ]);
                $location = District::create($validated);
                $name = $location->name;
                break;

            case 'upazila':
                $validated = $request->validate([
                    'district_id' => 'required|exists:districts,id',
                    'name' => 'required|string|max:255',
                    'bn_name' => 'required|string|max:255',
                ]);
                $location = Upazila::create($validated);
                $name = $location->name;
                break;

            case 'union':
                $validated = $request->validate([
                    'upazila_id' => 'required|exists:upazilas,id',
                    'name' => 'required|string|max:255',
                    'bn_name' => 'required|string|max:255',
                    'url' => 'nullable|string|max:255',
                ]);
                $location = Union::create($validated);
                $name = $location->name;
                break;

            case 'division':
            default:
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'bn_name' => 'required|string|max:255',
                    'lat' => 'nullable|string|max:255',
                    'long' => 'nullable|string|max:255',
                ]);
                $location = Division::create($validated);
                $name = $location->name;
                break;
        }

        return redirect()->route('locations.index', ['tab' => $type])
            ->with('success', ucfirst($type) . " \"{$name}\" created successfully.");
    }

    /**
     * Show the form for editing the specified location.
     */
    public function edit($type, $id)
    {
        $divisions = Division::orderBy('name')->get();
        $districts = District::with('division')->orderBy('name')->get();
        $upazilas = Upazila::with('district')->orderBy('name')->get();

        switch ($type) {
            case 'district':
                $location = District::findOrFail($id);
                break;
            case 'upazila':
                $location = Upazila::findOrFail($id);
                break;
            case 'union':
                $location = Union::findOrFail($id);
                break;
            case 'division':
            default:
                $location = Division::findOrFail($id);
                $type = 'division';
                break;
        }

        return view('locations.edit', compact('location', 'type', 'divisions', 'districts', 'upazilas'));
    }

    /**
     * Update the specified location.
     */
    public function update(Request $request, $type, $id)
    {
        switch ($type) {
            case 'district':
                $location = District::findOrFail($id);
                $validated = $request->validate([
                    'division_id' => 'required|exists:divisions,id',
                    'name' => 'required|string|max:255',
                    'bn_name' => 'required|string|max:255',
                    'lat' => 'nullable|string|max:255',
                    'long' => 'nullable|string|max:255',
                ]);
                break;

            case 'upazila':
                $location = Upazila::findOrFail($id);
                $validated = $request->validate([
                    'district_id' => 'required|exists:districts,id',
                    'name' => 'required|string|max:255',
                    'bn_name' => 'required|string|max:255',
                ]);
                break;

            case 'union':
                $location = Union::findOrFail($id);
                $validated = $request->validate([
                    'upazila_id' => 'required|exists:upazilas,id',
                    'name' => 'required|string|max:255',
                    'bn_name' => 'required|string|max:255',
                    'url' => 'nullable|string|max:255',
                ]);
                break;

            case 'division':
            default:
                $location = Division::findOrFail($id);
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'bn_name' => 'required|string|max:255',
                    'lat' => 'nullable|string|max:255',
                    'long' => 'nullable|string|max:255',
                ]);
                $type = 'division';
                break;
        }

        $location->update($validated);

        return redirect()->route('locations.index', ['tab' => $type])
            ->with('success', ucfirst($type) . " \"{$location->name}\" updated successfully.");
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy($type, $id)
    {
        switch ($type) {
            case 'district':
                $location = District::findOrFail($id);
                break;
            case 'upazila':
                $location = Upazila::findOrFail($id);
                break;
            case 'union':
                $location = Union::findOrFail($id);
                break;
            case 'division':
            default:
                $location = Division::findOrFail($id);
                $type = 'division';
                break;
        }

        $name = $location->name;
        $location->delete();

        return redirect()->route('locations.index', ['tab' => $type])
            ->with('success', ucfirst($type) . " \"{$name}\" deleted successfully.");
    }
}
