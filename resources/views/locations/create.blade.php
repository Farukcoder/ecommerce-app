@extends('tyro-dashboard::layouts.admin')

@section('title', 'Add New Location')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('locations.index') }}">Locations</a>
<span class="breadcrumb-separator">/</span>
<span>Add New Location</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add New Location</h1>
            <p class="page-description">Create a new division, district, upazila, or union with chained hierarchical selectors.</p>
        </div>
    </div>
</div>

<form action="{{ route('locations.store') }}" method="POST" id="location-form">
    @csrf

    <div class="card" style="max-width:780px;">
        <div class="card-header">
            <h3 class="card-title">Location Details</h3>
        </div>
        <div class="card-body">

            {{-- Location Type Selection --}}
            <div class="form-group">
                <label for="type" class="form-label">Location Type <span style="color:var(--destructive);">*</span></label>
                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required onchange="handleTypeChange(this.value)">
                    <option value="division" {{ old('type', $selectedType) === 'division' ? 'selected' : '' }}>Division</option>
                    <option value="district" {{ old('type', $selectedType) === 'district' ? 'selected' : '' }}>District</option>
                    <option value="upazila" {{ old('type', $selectedType) === 'upazila' ? 'selected' : '' }}>Upazila</option>
                    <option value="union" {{ old('type', $selectedType) === 'union' ? 'selected' : '' }}>Union</option>
                </select>
                @error('type')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            {{-- Chained Division Selector --}}
            <div class="form-group chained-group" id="group-chain-division" style="display:none;">
                <label for="chain-division" class="form-label">Division <span style="color:var(--destructive);">*</span></label>
                <select id="chain-division" class="form-select @error('division_id') is-invalid @enderror @error('temp_division_id') is-invalid @enderror">
                    <option value="">Select Division</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }} ({{ $division->bn_name }})</option>
                    @endforeach
                </select>
                @error('division_id')<span class="form-error">{{ $message }}</span>@enderror
                @error('temp_division_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            {{-- Chained District Selector --}}
            <div class="form-group chained-group" id="group-chain-district" style="display:none;">
                <label for="chain-district" class="form-label">District <span style="color:var(--destructive);">*</span></label>
                <select id="chain-district" class="form-select @error('district_id') is-invalid @enderror @error('temp_district_id') is-invalid @enderror">
                    <option value="">Select District</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" data-division-id="{{ $district->division_id }}">{{ $district->name }} ({{ $district->bn_name }})</option>
                    @endforeach
                </select>
                @error('district_id')<span class="form-error">{{ $message }}</span>@enderror
                @error('temp_district_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            {{-- Chained Upazila Selector --}}
            <div class="form-group chained-group" id="group-chain-upazila" style="display:none;">
                <label for="chain-upazila" class="form-label">Upazila <span style="color:var(--destructive);">*</span></label>
                <select id="chain-upazila" class="form-select @error('upazila_id') is-invalid @enderror">
                    <option value="">Select Upazila</option>
                    @foreach($upazilas as $upazila)
                        <option value="{{ $upazila->id }}" data-district-id="{{ $upazila->district_id }}" data-division-id="{{ $upazila->district->division_id ?? '' }}">{{ $upazila->name }} ({{ $upazila->bn_name }})</option>
                    @endforeach
                </select>
                @error('upazila_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            {{-- Common Name Fields --}}
            <div class="grid-2">
                <div class="form-group">
                    <label for="name" class="form-label">Name (English) <span style="color:var(--destructive);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="{{ __('messages.location_en_example') }}" required maxlength="255">
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="bn_name" class="form-label">Name (Bengali) <span style="color:var(--destructive);">*</span></label>
                    <input type="text" id="bn_name" name="bn_name" class="form-input @error('bn_name') is-invalid @enderror" value="{{ old('bn_name') }}" placeholder="{{ __('messages.location_bn_example') }}" required maxlength="255">
                    @error('bn_name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Latitude & Longitude (only for Division and District) --}}
            <div class="grid-2 type-dependent" id="group-lat-long" style="display:none;">
                <div class="form-group">
                    <label for="lat" class="form-label">Latitude <span class="form-label-optional">(optional)</span></label>
                    <input type="text" id="lat" name="lat" class="form-input @error('lat') is-invalid @enderror" value="{{ old('lat') }}" placeholder="e.g. 23.8103" maxlength="255">
                    @error('lat')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="long" class="form-label">Longitude <span class="form-label-optional">(optional)</span></label>
                    <input type="text" id="long" name="long" class="form-input @error('long') is-invalid @enderror" value="{{ old('long') }}" placeholder="e.g. 90.4125" maxlength="255">
                    @error('long')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- URL (only for Union) --}}
            <div class="form-group type-dependent" id="group-url" style="display:none;">
                <label for="url" class="form-label">Official Portal URL <span class="form-label-optional">(optional)</span></label>
                <input type="url" id="url" name="url" class="form-input @error('url') is-invalid @enderror" value="{{ old('url') }}" placeholder="e.g. https://union.gov.bd" maxlength="255">
                @error('url')<span class="form-error">{{ $message }}</span>@enderror
            </div>

        </div>
        <div class="card-footer" style="display:flex; gap:0.625rem; justify-content:flex-end;">
            <a href="{{ route('locations.index', ['tab' => $selectedType]) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Create Location
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
// Cache all options lists for client-side filtering
const districtOptions = Array.from(document.getElementById('chain-district').options);
const upazilaOptions = Array.from(document.getElementById('chain-upazila').options);

function handleTypeChange(type) {
    const divGroup = document.getElementById('group-chain-division');
    const distGroup = document.getElementById('group-chain-district');
    const upzGroup = document.getElementById('group-chain-upazila');

    const divSelect = document.getElementById('chain-division');
    const distSelect = document.getElementById('chain-district');
    const upzSelect = document.getElementById('chain-upazila');

    // Reset names and required constraints
    divSelect.removeAttribute('name');
    divSelect.removeAttribute('required');
    distSelect.removeAttribute('name');
    distSelect.removeAttribute('required');
    upzSelect.removeAttribute('name');
    upzSelect.removeAttribute('required');

    // Hide all groups by default
    divGroup.style.display = 'none';
    distGroup.style.display = 'none';
    upzGroup.style.display = 'none';
    document.getElementById('group-lat-long').style.display = 'none';
    document.getElementById('group-url').style.display = 'none';

    if (type === 'division') {
        document.getElementById('group-lat-long').style.display = 'grid';
    } else if (type === 'district') {
        // District requires Division selection
        divGroup.style.display = 'block';
        divSelect.setAttribute('name', 'division_id');
        divSelect.setAttribute('required', 'required');
        document.getElementById('group-lat-long').style.display = 'grid';
    } else if (type === 'upazila') {
        // Upazila requires Division -> District
        divGroup.style.display = 'block';
        distGroup.style.display = 'block';

        divSelect.setAttribute('name', 'temp_division_id');
        divSelect.setAttribute('required', 'required');

        distSelect.setAttribute('name', 'district_id');
        distSelect.setAttribute('required', 'required');

        filterDistricts(divSelect.value);
    } else if (type === 'union') {
        // Union requires Division -> District -> Upazila
        divGroup.style.display = 'block';
        distGroup.style.display = 'block';
        upzGroup.style.display = 'block';

        divSelect.setAttribute('name', 'temp_division_id');
        divSelect.setAttribute('required', 'required');

        distSelect.setAttribute('name', 'temp_district_id');
        distSelect.setAttribute('required', 'required');

        upzSelect.setAttribute('name', 'upazila_id');
        upzSelect.setAttribute('required', 'required');

        document.getElementById('group-url').style.display = 'block';

        filterDistricts(divSelect.value);
        filterUpazilas(distSelect.value);
    }
}

function filterDistricts(divisionId) {
    const distSelect = document.getElementById('chain-district');
    const currentValue = distSelect.value;

    // Clear out options
    distSelect.innerHTML = '';

    // Re-add options matching division_id
    const filtered = districtOptions.filter(opt => {
        return opt.value === '' || !divisionId || opt.dataset.divisionId === divisionId;
    });

    filtered.forEach(opt => distSelect.appendChild(opt));

    // Restore selection or reset
    if (filtered.some(opt => opt.value === currentValue)) {
        distSelect.value = currentValue;
    } else {
        distSelect.value = '';
    }

    // Cascade change to upazilas
    filterUpazilas(distSelect.value);
}

function filterUpazilas(districtId) {
    const upzSelect = document.getElementById('chain-upazila');
    const currentValue = upzSelect.value;
    const divisionId = document.getElementById('chain-division').value;

    // Clear out options
    upzSelect.innerHTML = '';

    // Re-add options matching district_id (or division_id as fallback)
    const filtered = upazilaOptions.filter(opt => {
        if (opt.value === '') return true;
        if (districtId) return opt.dataset.districtId === districtId;
        if (divisionId) return opt.dataset.divisionId === divisionId;
        return true;
    });

    filtered.forEach(opt => upzSelect.appendChild(opt));

    // Restore selection or reset
    if (filtered.some(opt => opt.value === currentValue)) {
        upzSelect.value = currentValue;
    } else {
        upzSelect.value = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('type');
    const divSelect = document.getElementById('chain-division');
    const distSelect = document.getElementById('chain-district');
    const upzSelect = document.getElementById('chain-upazila');

    // Register event listeners to handle chained filtering
    divSelect.addEventListener('change', () => filterDistricts(divSelect.value));
    distSelect.addEventListener('change', () => filterUpazilas(distSelect.value));

    // Restore old values (e.g. from validation fail redirect)
    const oldType = "{{ old('type', $selectedType) }}";
    typeSelect.value = oldType;

    const oldDivId = "{{ old('division_id') ?: old('temp_division_id') }}";
    const oldDistId = "{{ old('district_id') ?: old('temp_district_id') }}";
    const oldUpzId = "{{ old('upazila_id') }}";

    if (oldDivId) divSelect.value = oldDivId;
    filterDistricts(oldDivId);

    if (oldDistId) distSelect.value = oldDistId;
    filterUpazilas(oldDistId);

    if (oldUpzId) upzSelect.value = oldUpzId;

    // Apply layout constraints
    handleTypeChange(oldType);
});
</script>
@endpush

@endsection
