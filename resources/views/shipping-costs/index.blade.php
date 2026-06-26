@extends('tyro-dashboard::layouts.admin')

@section('title', 'Shipping Cost Setup')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">{{ __('messages.dashboard') }}</a>
<span class="breadcrumb-separator">/</span>
<span>Shipping Cost Setup</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Shipping Cost Setup</h1>
            <p class="page-description">Configure shipping costs for different districts across Bangladesh</p>
        </div>
    </div>
</div>

<style>
    .division-link:hover {
        background-color: var(--primary);
    }
    
    .division-link:hover span {
        color: white;
    }
    
    .dark .division-link:hover span {
        color: black;
    }
</style>

<div class="grid-2" style="grid-template-columns: 280px 1fr; gap: 1.5rem; align-items: start;">
    {{-- Left Sidebar - Divisions --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">DIVISIONS</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="max-height: 600px; overflow-y: auto;">
                @foreach($divisions as $division)
                    <a href="{{ route('shipping-costs.index', ['division_id' => $division->id]) }}"
                       class="sidebar-link division-link {{ $selectedDivision->id === $division->id ? 'active' : '' }}"
                       style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); color: var(--foreground); text-decoration: none; transition: background-color 0.15s ease, color 0.15s ease;">
                        <span style="font-weight: 500; transition: color 0.15s ease;">{{ $division->name }}</span>
                        <span class="badge badge-secondary" style="transition: color 0.15s ease;">{{ $division->districts->count() }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div>
        {{-- System Settings Card --}}
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Global Settings</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('shipping-costs.system-settings.update') }}" method="POST">
                    @csrf
                    
                    {{-- Free Shipping for Everyone Toggle --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid var(--border);">
                        <div>
                            <div style="font-weight: 600; margin-bottom: 0.25rem; color: var(--foreground);">Free shipping for everyone</div>
                            <div style="font-size: 0.875rem; color: var(--muted-foreground);">Enable free shipping for all districts across Bangladesh</div>
                        </div>
                        <input type="hidden" name="free_shipping_for_everyone" value="0">
                        <label class="toggle-label" style="margin: 0;">
                            <input type="checkbox" name="free_shipping_for_everyone" value="1" class="toggle-input" {{ $systemSetting?->free_shipping_for_everyone ? 'checked' : '' }} onchange="this.form.requestSubmit()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    {{-- Default Rate Section --}}
                    <div style="padding: 1rem 0;">
                        <div style="font-weight: 600; margin-bottom: 0.5rem; color: var(--foreground);">Others - default rate</div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="position: relative; flex: 1; max-width: 200px;">
                                <input type="number" name="default_shipping_rate" 
                                       value="{{ $systemSetting?->default_shipping_rate ?? 80 }}"
                                       step="0.01" min="0"
                                       class="form-input" 
                                       style="padding-left: 2rem;">
                                <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--muted-foreground);">৳</span>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                        <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-top: 0.5rem;">
                            This rate will be applied to districts without custom shipping rates
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Rate Coverage Progress --}}
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-body" style="padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <span style="font-weight: 600; font-size: 0.875rem; color: var(--foreground);">RATE COVERAGE ACROSS 64 DISTRICTS</span>
                    <span style="font-size: 0.875rem; color: var(--muted-foreground);">{{ $stats['total'] }} total</span>
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <div style="flex: 1; background: var(--border); border-radius: 4px; overflow: hidden; height: 8px;">
                        <div style="width: {{ ($stats['free_shipping'] / max($stats['total'], 1)) * 100 }}%; background: var(--success); height: 100%;"></div>
                    </div>
                    <div style="flex: 1; background: var(--border); border-radius: 4px; overflow: hidden; height: 8px;">
                        <div style="width: {{ ($stats['custom_rate'] / max($stats['total'], 1)) * 100 }}%; background: var(--primary); height: 100%;"></div>
                    </div>
                    <div style="flex: 1; background: var(--border); border-radius: 4px; overflow: hidden; height: 8px;">
                        <div style="width: {{ ($stats['using_default'] / max($stats['total'], 1)) * 100 }}%; background: var(--muted-foreground); height: 100%;"></div>
                    </div>
                </div>

                <div style="display: flex; gap: 1.5rem; font-size: 0.875rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="width: 10px; height: 10px; background: var(--success); border-radius: 50%;"></span>
                        <span style="color: var(--foreground);">Free shipping ({{ $stats['free_shipping'] }})</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="width: 10px; height: 10px; background: var(--primary); border-radius: 50%;"></span>
                        <span style="color: var(--foreground);">Custom rate set ({{ $stats['custom_rate'] }})</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="width: 10px; height: 10px; background: var(--muted-foreground); border-radius: 50%;"></span>
                        <span style="color: var(--foreground);">Using Others default ({{ $stats['using_default'] }})</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- District List Card --}}
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="card-title">{{ $selectedDivision->name }} {{ $selectedDivision->districts->count() }} districts</h3>
                </div>
                <form action="{{ route('shipping-costs.divisions.make-free', $selectedDivision->id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure you want to make all districts in {{ $selectedDivision->name }} free shipping?')">
                        Make division free
                    </button>
                </form>
            </div>
            <div class="card-body" style="padding: 0;">
                {{-- Search Bar --}}
                <div style="padding: 1rem; border-bottom: 1px solid var(--border);">
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="districtSearch" 
                               class="form-input" 
                               placeholder="Search district..." 
                               onkeyup="filterDistricts()">
                    </div>
                </div>

                @if($districts->count())
                    {{-- District Table --}}
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">District</th>
                                    <th style="width: 20%;">Status</th>
                                    <th style="width: 30%;">Shipping Cost</th>
                                    <th style="width: 20%;">Free</th>
                                </tr>
                            </thead>
                            <tbody id="districtTableBody">
                                @foreach($districts as $district)
                                    <tr class="district-row" data-district-name="{{ strtolower($district->name) }}">
                                        <td>
                                            <div style="font-weight: 500; color: var(--foreground);">{{ $district->name }}</div>
                                        </td>
                                        <td>
                                            @if($district->shippingCost && $district->shippingCost->is_free)
                                                <span class="badge badge-success">Free</span>
                                            @elseif($district->shippingCost && $district->shippingCost->cost > 0)
                                                <span class="badge badge-primary">Custom</span>
                                            @else
                                                <span class="badge badge-secondary">OTHERS</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('shipping-costs.districts.update', $district) }}" method="POST" class="district-cost-form">
                                                @csrf
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <div style="position: relative; flex: 1;">
                                                        <input type="number" name="cost" 
                                                               value="{{ $district->shippingCost?->cost ?? ($systemSetting?->default_shipping_rate ?? 80) }}"
                                                               step="0.01" min="0"
                                                               class="form-input" 
                                                               style="padding-left: 2rem;"
                                                               onchange="this.form.requestSubmit()">
                                                        <span style="position: absolute; left: 0.5rem; top: 50%; transform: translateY(-50%); color: var(--muted-foreground);">৳</span>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="{{ route('shipping-costs.districts.update', $district) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="cost" value="{{ $district->shippingCost?->cost ?? ($systemSetting?->default_shipping_rate ?? 80) }}">
                                                <input type="hidden" name="is_free" value="0">
                                                <label class="toggle-label" style="margin: 0;">
                                                    <input type="checkbox" name="is_free" value="1" class="toggle-input"
                                                           {{ $district->shippingCost?->is_free ? 'checked' : '' }}
                                                           onchange="this.form.requestSubmit()">
                                                    <span class="toggle-slider"></span>
                                                </label>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="empty-state">
                        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <h3 class="empty-state-title">No districts found</h3>
                        <p class="empty-state-description">This division has no districts configured.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterDistricts() {
    const searchTerm = document.getElementById('districtSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.district-row');
    
    rows.forEach(row => {
        const districtName = row.getAttribute('data-district-name');
        if (districtName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endpush
@endsection
