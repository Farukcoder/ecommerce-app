@extends('tyro-dashboard::layouts.admin')

@section('title', 'Locations')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Locations</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Locations</h1>
            <p class="page-description">Manage administrative divisions, districts, upazilas, and unions.</p>
        </div>
        <a href="{{ route('locations.create', ['type' => $tab]) }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add {{ ucfirst($tab) }}
        </a>
    </div>
</div>

{{-- Navigation Tabs --}}
<div class="tabs" style="margin-bottom: 1rem;">
    <a href="{{ route('locations.index', ['tab' => 'division']) }}" class="tab-link {{ $tab === 'division' ? 'active' : '' }}">Divisions</a>
    <a href="{{ route('locations.index', ['tab' => 'district']) }}" class="tab-link {{ $tab === 'district' ? 'active' : '' }}">Districts</a>
    <a href="{{ route('locations.index', ['tab' => 'upazila']) }}" class="tab-link {{ $tab === 'upazila' ? 'active' : '' }}">Upazilas</a>
    <a href="{{ route('locations.index', ['tab' => 'union']) }}" class="tab-link {{ $tab === 'union' ? 'active' : '' }}">Unions</a>
</div>

{{-- Search & Filters --}}
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <form action="{{ route('locations.index') }}" method="GET">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="form-input" placeholder="Search by name or Bengali name…" value="{{ $filters['search'] ?? '' }}">
                </div>

                @if($tab === 'district')
                <div class="filter-group">
                    <label class="filter-label">Division:</label>
                    <select name="division_id" class="form-select" style="min-width: 160px;">
                        <option value="">All Divisions</option>
                        @foreach($divisionsList as $division)
                            <option value="{{ $division->id }}" {{ ($filters['division_id'] ?? '') == $division->id ? 'selected' : '' }}>
                                {{ $division->name }} ({{ $division->bn_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($tab === 'upazila')
                <div class="filter-group">
                    <label class="filter-label">Division:</label>
                    <select name="division_id" id="filter-division_id" class="form-select" style="min-width: 150px;">
                        <option value="">All Divisions</option>
                        @foreach($divisionsList as $division)
                            <option value="{{ $division->id }}" {{ ($filters['division_id'] ?? '') == $division->id ? 'selected' : '' }}>
                                {{ $division->name }} ({{ $division->bn_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">District:</label>
                    <select name="district_id" id="filter-district_id" class="form-select" style="min-width: 150px;">
                        <option value="">All Districts</option>
                        @foreach($districtsList as $district)
                            <option value="{{ $district->id }}" data-division-id="{{ $district->division_id }}" {{ ($filters['district_id'] ?? '') == $district->id ? 'selected' : '' }}>
                                {{ $district->name }} ({{ $district->bn_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($tab === 'union')
                <div class="filter-group">
                    <label class="filter-label">Division:</label>
                    <select name="division_id" id="filter-division_id" class="form-select" style="min-width: 130px;">
                        <option value="">All Divisions</option>
                        @foreach($divisionsList as $division)
                            <option value="{{ $division->id }}" {{ ($filters['division_id'] ?? '') == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">District:</label>
                    <select name="district_id" id="filter-district_id" class="form-select" style="min-width: 130px;">
                        <option value="">All Districts</option>
                        @foreach($districtsList as $district)
                            <option value="{{ $district->id }}" data-division-id="{{ $district->division_id }}" {{ ($filters['district_id'] ?? '') == $district->id ? 'selected' : '' }}>
                                {{ $district->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Upazila:</label>
                    <select name="upazila_id" id="filter-upazila_id" class="form-select" style="min-width: 130px;">
                        <option value="">All Upazilas</option>
                        @foreach($upazilasList as $upazila)
                            <option value="{{ $upazila->id }}" data-district-id="{{ $upazila->district_id }}" data-division-id="{{ $upazila->district->division_id ?? '' }}" {{ ($filters['upazila_id'] ?? '') == $upazila->id ? 'selected' : '' }}>
                                {{ $upazila->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <button type="submit" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filter
                </button>
                
                @if(array_filter($filters))
                    <a href="{{ route('locations.index', ['tab' => $tab]) }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Locations Table --}}
<div class="card">
    @if($items->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:80px;">ID</th>
                    <th>Name</th>
                    <th>Bengali Name</th>
                    
                    @if($tab === 'district')
                        <th>Division</th>
                    @elseif($tab === 'upazila')
                        <th>District</th>
                    @elseif($tab === 'union')
                        <th>Upazila</th>
                    @endif

                    @if($tab === 'division' || $tab === 'district')
                        <th>Latitude</th>
                        <th>Longitude</th>
                    @elseif($tab === 'union')
                        <th>URL</th>
                    @endif

                    <th style="text-align:right; width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>
                        <span style="font-weight:600; color:var(--muted-foreground);">#{{ $item->id }}</span>
                    </td>
                    <td>
                        <div style="font-weight:500; font-size:0.9375rem; color:var(--foreground);">{{ $item->name }}</div>
                    </td>
                    <td>
                        <div style="font-size:0.9375rem; color:var(--foreground);">{{ $item->bn_name }}</div>
                    </td>

                    @if($tab === 'district')
                        <td>
                            <span class="badge badge-secondary">{{ $item->division->name ?? '—' }}</span>
                        </td>
                    @elseif($tab === 'upazila')
                        <td>
                            <span class="badge badge-secondary">{{ $item->district->name ?? '—' }}</span>
                            <div style="font-size:0.75rem; color:var(--muted-foreground); margin-top:2px;">{{ $item->district->division->name ?? '' }}</div>
                        </td>
                    @elseif($tab === 'union')
                        <td>
                            <span class="badge badge-secondary">{{ $item->upazila->name ?? '—' }}</span>
                            <div style="font-size:0.75rem; color:var(--muted-foreground); margin-top:2px;">
                                {{ $item->upazila->district->name ?? '' }}
                                @if(isset($item->upazila->district->division->name))
                                    · {{ $item->upazila->district->division->name }}
                                @endif
                            </div>
                        </td>
                    @endif

                    @if($tab === 'division' || $tab === 'district')
                        <td>{{ $item->lat ?: '—' }}</td>
                        <td>{{ $item->long ?: '—' }}</td>
                    @elseif($tab === 'union')
                        <td>
                            @if($item->url)
                                <a href="{{ $item->url }}" target="_blank" style="font-size:0.8125rem; color:var(--primary); text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                    Visit
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    @endif

                    <td>
                        <div class="action-buttons" style="justify-content:flex-end;">
                            <a href="{{ route('locations.edit', ['type' => $tab, 'id' => $item->id]) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('locations.destroy', ['type' => $tab, 'id' => $item->id]) }}" method="POST" style="display:inline;" id="delete-location-{{ $item->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="action-btn action-btn-danger" title="Delete"
                                    onclick="showDanger('Delete {{ ucfirst($tab) }}', 'Are you sure you want to delete &quot;{{ addslashes($item->name) }}&quot;? All child locations linked to this will also be deleted if database constraints allow.').then(confirmed => { if(confirmed) document.getElementById('delete-location-{{ $item->id }}').submit(); })">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
        <div style="font-size:0.875rem; color:var(--muted-foreground);">
            Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }} items
        </div>
        <div class="pagination">
            {{ $items->links() }}
        </div>
    </div>
    @endif

    @else
    {{-- Empty State --}}
    <div class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
        </svg>
        <h3 class="empty-state-title">No {{ str_replace('_', ' ', $tab) }}s found</h3>
        <p class="empty-state-description">
            @if(array_filter($filters))
                No records match your filters.
            @else
                Get started by adding the first {{ str_replace('_', ' ', $tab) }}.
            @endif
        </p>
        <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('locations.create', ['type' => $tab]) }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add {{ ucfirst($tab) }}
            </a>
            @if(array_filter($filters))
                <a href="{{ route('locations.index', ['tab' => $tab]) }}" class="btn btn-secondary">Clear Filters</a>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const divFilter = document.getElementById('filter-division_id');
    const distFilter = document.getElementById('filter-district_id');
    const upzFilter = document.getElementById('filter-upazila_id');

    const districtOptions = distFilter ? Array.from(distFilter.options) : [];
    const upazilaOptions = upzFilter ? Array.from(upzFilter.options) : [];

    function filterDistrictOptions(divisionId) {
        if (!distFilter) return;
        const currentValue = distFilter.value;
        distFilter.innerHTML = '';
        
        const filtered = districtOptions.filter(opt => {
            return opt.value === '' || !divisionId || opt.dataset.divisionId === divisionId;
        });
        
        filtered.forEach(opt => distFilter.appendChild(opt));
        
        if (filtered.some(opt => opt.value === currentValue)) {
            distFilter.value = currentValue;
        } else {
            distFilter.value = '';
        }
        
        filterUpazilaOptions(distFilter.value);
    }

    function filterUpazilaOptions(districtId) {
        if (!upzFilter) return;
        const currentValue = upzFilter.value;
        const divisionId = divFilter ? divFilter.value : '';
        upzFilter.innerHTML = '';
        
        const filtered = upazilaOptions.filter(opt => {
            if (opt.value === '') return true;
            if (districtId) return opt.dataset.districtId === districtId;
            if (divisionId) return opt.dataset.divisionId === divisionId;
            return true;
        });
        
        filtered.forEach(opt => upzFilter.appendChild(opt));
        
        if (filtered.some(opt => opt.value === currentValue)) {
            upzFilter.value = currentValue;
        } else {
            upzFilter.value = '';
        }
    }

    if (divFilter) {
        divFilter.addEventListener('change', () => {
            filterDistrictOptions(divFilter.value);
        });
    }

    if (distFilter) {
        distFilter.addEventListener('change', () => {
            filterUpazilaOptions(distFilter.value);
        });
    }

    // Set initial filtered state on page load
    const initialDivId = "{{ $filters['division_id'] ?? '' }}";
    const initialDistId = "{{ $filters['district_id'] ?? '' }}";
    const initialUpzId = "{{ $filters['upazila_id'] ?? '' }}";

    if (divFilter && initialDivId) {
        divFilter.value = initialDivId;
        filterDistrictOptions(initialDivId);
    }
    if (distFilter && initialDistId) {
        distFilter.value = initialDistId;
        filterUpazilaOptions(initialDistId);
    }
    if (upzFilter && initialUpzId) {
        upzFilter.value = initialUpzId;
    }
});
</script>
@endpush
