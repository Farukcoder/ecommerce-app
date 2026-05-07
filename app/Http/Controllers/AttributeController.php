<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes.
     */
    public function index(Request $request)
    {
        $query = Attribute::withCount('values')->with(['values' => fn ($q) => $q->orderBy('value')]);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $attributes = $query->latest()->paginate(15)->withQueryString();
        $filters    = $request->only(['search']);

        return view('attributes.index', compact('attributes', 'filters'));
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create()
    {
        return view('attributes.create');
    }

    /**
     * Store a newly created attribute.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:attributes,name',
            'values'   => 'array',
            'values.*' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $attribute = Attribute::create(['name' => $validated['name']]);
            $this->syncValues($attribute, $validated['values'] ?? []);
        });

        return redirect()->route('attributes.index')
            ->with('success', "Attribute \"{$validated['name']}\" created successfully.");
    }

    /**
     * Show the form for editing the specified attribute.
     */
    public function edit(Attribute $attribute)
    {
        $attribute->load(['values' => fn ($q) => $q->orderBy('id')]);

        return view('attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name'     => "required|string|max:255|unique:attributes,name,{$attribute->id}",
            'values'   => 'array',
            'values.*' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($attribute, $validated) {
            $attribute->update(['name' => $validated['name']]);
            $this->syncValues($attribute, $validated['values'] ?? []);
        });

        return redirect()->route('attributes.index')
            ->with('success', "Attribute \"{$attribute->name}\" updated successfully.");
    }

    /**
     * Remove the specified attribute.
     */
    public function destroy(Attribute $attribute)
    {
        $name = $attribute->name;
        $attribute->delete(); // cascades to attribute_values via FK

        return redirect()->route('attributes.index')
            ->with('success', "Attribute \"{$name}\" deleted successfully.");
    }

    /**
     * Reconcile the attribute's values with the submitted list.
     *
     * - Trims & dedupes input.
     * - Keeps existing rows whose value still appears (no churn on FK references).
     * - Creates new rows for new values.
     * - Deletes rows whose value was removed.
     */
    protected function syncValues(Attribute $attribute, array $values): void
    {
        $clean = collect($values)
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        $existing = $attribute->values()->get()->keyBy('value');

        $keepIds = [];

        foreach ($clean as $value) {
            if ($existing->has($value)) {
                $keepIds[] = $existing[$value]->id;
                continue;
            }
            $row = $attribute->values()->create(['value' => $value]);
            $keepIds[] = $row->id;
        }

        AttributeValue::where('attribute_id', $attribute->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
