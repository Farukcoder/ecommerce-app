@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Location')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('locations.index') }}">Locations</a>
<span class="breadcrumb-separator">/</span>
<span>Edit Location</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Location</h1>
            <p class="page-description">Modify an existing administrative {{ $type }} with chained parent dropdown selectors.</p>
        </div>
    </div>
</div>

<form action="{{ route('locations.update', ['type' => $type, 'id' => $location->id]) }}" method="POST" id="location-form">
    @csrf
    @method('PUT')

    <div class="card" style="max-width:780px;">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 class="card-title">Location Details</h3>
            <span class="badge badge-primary" style="text-transform:uppercase;">{{ $type }}</span>
        </div>
        <div class="card-body">

            {{-- Chained Division Selector --}}
            @if($type === 'district' || $type === 'upazila' || $type === 'union')
            <div class="form-group chained-group" id="group-chain-division">
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
            @endif

            {{-- Chained District Selector --}}
            @if($type === 'upazila' || $type === 'union')
            <div class="form-group chained-group" id="group-chain-district">
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
            @endif

            {{-- Chained Upazila Selector --}}
            @if($type === 'union')
            <div class="form-group chained-group" id="group-chain-upazila">
                <label for="chain-upazila" class="form-label">Upazila <span style="color:var(--destructive);">*</span></label>
                <select id="chain-upazila" class="form-select @error('upazila_id') is-invalid @enderror">
                    <option value="">Select Upazila</option>
                    @foreach($upazilas as $upazila)
                        <option value="{{ $upazila->id }}" data-district-id="{{ $upazila->district_id }}" data-division-id="{{ $upazila->district->division_id ?? '' }}">{{ $upazila->name }} ({{ $upazila->bn_name }})</option>
                    @endforeach
                </select>
                @error('upazila_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            @endif

            {{-- Common Name Fields --}}
            <div class="grid-2">
                <div class="form-group">
                    <label for="name" class="form-label">Name (English) <span style="color:var(--destructive);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name', $location->name) }}" placeholder="e.g. Mirpur" required maxlength="255">
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="bn_name" class="form-label">Name (Bengali) <span style="color:var(--destructive);">*</span></label>
                    <input type="text" id="bn_name" name="bn_name" class="form-input @error('bn_name') is-invalid @enderror" value="{{ old('bn_name', $location->bn_name) }}" placeholder="e.g. মিরপুর" required maxlength="255">
                    @error('bn_name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Latitude & Longitude (only for Division and District) --}}
            @if($type === 'division' || $type === 'district')
            <div class="grid-2">
                <div class="form-group">
                    <label for="lat" class="form-label">Latitude <span class="form-label-optional">(optional)</span></label>
                    <input type="text" id="lat" name="lat" class="form-input @error('lat') is-invalid @enderror" value="{{ old('lat', $location->lat) }}" placeholder="e.g. 23.8103" maxlength="255">
                    @error('lat')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="long" class="form-label">Longitude <span class="form-label-optional">(optional)</span></label>
                    <input type="text" id="long" name="long" class="form-input @error('long') is-invalid @enderror" value="{{ old('long', $location->long) }}" placeholder="e.g. 90.4125" maxlength="255">
                    @error('long')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
            @endif

            {{-- URL (only for Union) --}}
            @if($type === 'union')
            <div class="form-group">
                <label for="url" class="form-label">Official Portal URL <span class="form-label-optional">(optional)</span></label>
                <input type="url" id="url" name="url" class="form-input @error('url') is-invalid @enderror" value="{{ old('url', $location->url) }}" placeholder="e.g. https://union.gov.bd" maxlength="255">
                @error('url')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            @endif

        </div>
        <div class="card-footer" style="display:flex; gap:0.625rem; justify-content:flex-end;">
            <a href="{{ route('locations.index', ['tab' => $type]) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Update Location
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
// Cache all options lists for client-side filtering if they exist
const districtSelectEl = document.getElementById('chain-district');
const upazilaSelectEl = document.getElementById('chain-upazila');

const districtOptions = districtSelectEl ? Array.from(districtSelectEl.options) : [];
const upazilaOptions = upazilaSelectEl ? Array.from(upazilaSelectEl.options) : [];

function setupNamesAndRequired(type) {
    const divSelect = document.getElementById('chain-division');
    const distSelect = document.getElementById('chain-district');
    const upzSelect = document.getElementById('chain-upazila');

    if (divSelect) {
        divSelect.removeAttribute('name');
        divSelect.removeAttribute('required');
    }
    if (distSelect) {
        distSelect.removeAttribute('name');
        distSelect.removeAttribute('required');
    }
    if (upzSelect) {
        upzSelect.removeAttribute('name');
        upzSelect.removeAttribute('required');
    }

    if (type === 'district') {
        divSelect.setAttribute('name', 'division_id');
        divSelect.setAttribute('required', 'required');
    } else if (type === 'upazila') {
        divSelect.setAttribute('name', 'temp_division_id');
        divSelect.setAttribute('required', 'required');
        distSelect.setAttribute('name', 'district_id');
        distSelect.setAttribute('required', 'required');
    } else if (type === 'union') {
        divSelect.setAttribute('name', 'temp_division_id');
        divSelect.setAttribute('required', 'required');
        distSelect.setAttribute('name', 'temp_district_id');
        distSelect.setAttribute('required', 'required');
        upzSelect.setAttribute('name', 'upazila_id');
        upzSelect.setAttribute('required', 'required');
    }
}

function filterDistricts(divisionId) {
    const distSelect = document.getElementById('chain-district');
    if (!distSelect) return;
    const currentValue = distSelect.value;
    
    // Clear options
    distSelect.innerHTML = '';
    
    // Filter
    const filtered = districtOptions.filter(opt => {
        return opt.value === '' || !divisionId || opt.dataset.divisionId === divisionId;
    });
    
    filtered.forEach(opt => distSelect.appendChild(opt));
    
    // Restore or reset
    if (filtered.some(opt => opt.value === currentValue)) {
        distSelect.value = currentValue;
    } else {
        distSelect.value = '';
    }
    
    // Cascade
    filterUpazilas(distSelect.value);
}

function filterUpazilas(districtId) {
    const upzSelect = document.getElementById('chain-upazila');
    if (!upzSelect) return;
    const currentValue = upzSelect.value;
    const divSelect = document.getElementById('chain-division');
    const divisionId = divSelect ? divSelect.value : '';
    
    // Clear options
    upzSelect.innerHTML = '';
    
    // Filter
    const filtered = upazilaOptions.filter(opt => {
        if (opt.value === '') return true;
        if (districtId) return opt.dataset.districtId === districtId;
        if (divisionId) return opt.dataset.divisionId === divisionId;
        return true;
    });
    
    filtered.forEach(opt => upzSelect.appendChild(opt));
    
    // Restore or reset
    if (filtered.some(opt => opt.value === currentValue)) {
        upzSelect.value = currentValue;
    } else {
        upzSelect.value = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const divSelect = document.getElementById('chain-division');
    const distSelect = document.getElementById('chain-district');
    const upzSelect = document.getElementById('chain-upazila');

    // Register filters
    if (divSelect) {
        divSelect.addEventListener('change', () => filterDistricts(divSelect.value));
    }
    if (distSelect) {
        distSelect.addEventListener('change', () => filterUpazilas(distSelect.value));
    }

    // Initialize names and validation
    const type = "{{ $type }}";
    setupNamesAndRequired(type);

    // Eagerly set selections based on editing location values or old inputs
    const oldDivId = "{{ old('division_id') ?: old('temp_division_id') }}" || 
                     "{{ ($type === 'district' ? $location->division_id : ($type === 'upazila' ? $location->district->division_id : ($type === 'union' ? $location->upazila->district->division_id : ''))) }}";

    const oldDistId = "{{ old('district_id') ?: old('temp_district_id') }}" ||
                      "{{ ($type === 'upazila' ? $location->district_id : ($type === 'union' ? $location->upazila->district_id : '')) }}";

    const oldUpzId = "{{ old('upazila_id') }}" ||
                     "{{ ($type === 'union' ? $location->upazila_id : '') }}";

    if (divSelect && oldDivId) {
        divSelect.value = oldDivId;
        filterDistricts(oldDivId);
    }

    if (distSelect && oldDistId) {
        distSelect.value = oldDistId;
        filterUpazilas(oldDistId);
    }

    if (upzSelect && oldUpzId) {
        upzSelect.value = oldUpzId;
    }
});
</script>
@endpush

@endsection
