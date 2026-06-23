@extends('tyro-dashboard::layouts.user')

@section('title', __('messages.products'))

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">{{ __('messages.dashboard') }}</a>
<span class="breadcrumb-separator">/</span>
<span>{{ __('messages.products') }}</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ __('messages.products') }}</h1>
            <p class="page-description">{{ __('messages.manage_catalog_description') }}</p>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <a href="{{ route('products.import.form') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-3-3m3 3l3-3M5 21h14" />
                </svg>
                {{ __('messages.bulk_upload') }}
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ __('messages.add_product') }}
            </a>
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V11"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.total_products') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $products->total() }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.published') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $products->getCollection()->where('status', 'published')->count() }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.drafts') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $products->getCollection()->where('status', 'draft')->count() }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.archived') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $products->getCollection()->where('status', 'archived')->count() }}</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <form action="{{ route('products.index') }}" method="GET">
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="form-input" placeholder="{{ __('messages.search_placeholder') }}" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="filter-group">
                    <label class="filter-label">{{ __('messages.status') }}:</label>
                    <select name="status" class="form-select" style="min-width: 140px;">
                        <option value="">{{ __('messages.all_status') }}</option>
                        <option value="published" {{ strtolower($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>{{ __('messages.published') }}</option>
                        <option value="draft"     {{ strtolower($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>{{ __('messages.draft') }}</option>
                        <option value="archived"  {{ strtolower($filters['status'] ?? '') === 'archived' ? 'selected' : '' }}>{{ __('messages.archived') }}</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">{{ __('messages.category') }}:</label>
                    <select name="category" class="form-select" style="min-width: 150px;">
                        <option value="">{{ __('messages.all_categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">{{ __('messages.brand') }}:</label>
                    <select name="brand" class="form-select" style="min-width: 140px;">
                        <option value="">{{ __('messages.all_brands') }}</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ ($filters['brand'] ?? '') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    {{ __('messages.filter') }}
                </button>
                @if(array_filter($filters))
                    <a href="{{ route('products.index') }}" class="btn btn-ghost">{{ __('messages.clear') }}</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Products Table --}}
<div class="card">
    @if($products->count())

    {{-- Bulk Action Bar --}}
    <div class="card-header" style="padding: 0.875rem 1.5rem;" id="bulk-bar" style="display:none;">
        <div style="display:flex; align-items:center; gap:0.75rem; width:100%;">
            <input type="checkbox" id="select-all" class="checkbox-input" style="cursor:pointer;">
            <label for="select-all" style="font-size:0.875rem; color:var(--muted-foreground); cursor:pointer;">
                {{ __('messages.select_all_page') }}
            </label>
            <span id="selected-count" style="font-size:0.875rem; color:var(--muted-foreground); margin-left:0.5rem;"></span>
            <div style="margin-left:auto; display:flex; gap:0.5rem;">
                <button type="button" class="btn btn-sm btn-secondary" id="bulk-publish-btn" disabled>{{ __('messages.publish_selected') }}</button>
                <button type="button" class="btn btn-sm btn-secondary" id="bulk-draft-btn" disabled>{{ __('messages.set_draft') }}</button>
                <button type="button" class="btn btn-sm" style="background:color-mix(in srgb, var(--destructive), transparent 88%); color:var(--destructive);" id="bulk-delete-btn" disabled>{{ __('messages.delete_selected') }}</button>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="products-table">
            <thead>
                <tr>
                    <th style="width:40px; padding-right:0;">
                        <input type="checkbox" id="select-all-th" class="checkbox-input" style="cursor:pointer;">
                    </th>
                    <th>{{ __('messages.products') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th>{{ __('messages.brand') }}</th>
                    <th>{{ __('messages.price') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.featured') }}</th>
                    <th style="text-align:right;">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr data-product-id="{{ $product->id }}">
                    <td style="padding-right:0; width:40px;">
                        <input type="checkbox" class="checkbox-input product-checkbox" value="{{ $product->id }}" style="cursor:pointer;">
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            @php
                                $listImage = $product->thumbnail ?: optional($product->images->sortByDesc('is_primary')->first())->image;
                            @endphp
                            <div style="width:44px; height:44px; border-radius:8px; background:var(--muted); border:1px solid var(--border); flex-shrink:0; display:flex; align-items:center; justify-content:center; color:var(--muted-foreground); overflow:hidden;">
                                @if($listImage)
                                    <img src="{{ asset('storage/' . $listImage) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:20px;height:20px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <div style="font-weight:500; font-size:0.9375rem; color:var(--foreground);">{{ $product->name }}</div>
                                @if($product->short_description)
                                    <div style="font-size:0.8125rem; color:var(--muted-foreground); margin-top:2px; max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        {{ $product->short_description }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($product->sku)
                            <code style="font-size:0.8125rem; background:var(--muted); padding:2px 6px; border-radius:4px; color:var(--muted-foreground);">{{ $product->sku }}</code>
                        @else
                            <span style="color:var(--muted-foreground); font-size:0.8125rem;">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="badge-list">
                            @forelse($product->categories->take(2) as $cat)
                                <span class="badge badge-secondary">{{ $cat->name }}</span>
                            @empty
                                <span style="color:var(--muted-foreground); font-size:0.8125rem;">—</span>
                            @endforelse
                            @if($product->categories->count() > 2)
                                <span class="badge badge-secondary">+{{ $product->categories->count() - 2 }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($product->brand)
                            <span style="font-size:0.875rem; color:var(--foreground);">{{ $product->brand->name }}</span>
                        @else
                            <span style="color:var(--muted-foreground); font-size:0.8125rem;">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:0.9375rem; font-weight:600; color:var(--foreground);">
                            @money($product->base_price)
                        </div>
                        @if($product->sale_price)
                            <div style="font-size:0.8125rem; color:var(--success);">
                                {{ __('messages.sale_price') }}@money($product->sale_price)
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($product->status === 'published')
                            <span class="badge badge-success">{{ __('messages.published') }}</span>
                        @elseif($product->status === 'draft')
                            <span class="badge badge-warning">{{ __('messages.draft') }}</span>
                        @else
                            <span class="badge badge-secondary">{{ __('messages.archived') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($product->featured)
                            <span class="badge badge-primary">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width:12px;height:12px;margin-right:3px;">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                {{ __('messages.featured') }}
                            </span>
                        @else
                            <span style="color:var(--muted-foreground); font-size:0.8125rem;">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content:flex-end;">
                            <a href="{{ route('products.edit', $product) }}" class="action-btn" title="{{ __('messages.edit') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="{{ route('products.show', $product) }}" class="action-btn" title="{{ __('messages.preview') }}" target="_blank">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline;" id="delete-product-{{ $product->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="action-btn action-btn-danger" title="{{ __('messages.delete') }}"
                                    onclick="showDanger('{{ __('messages.delete') }}', '{{ __('messages.product_delete_confirm') }}').then(confirmed => { if(confirmed) document.getElementById('delete-product-{{ $product->id }}').submit(); })">
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
    @if($products->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
        <div style="font-size:0.875rem; color:var(--muted-foreground);">
            {{ __('messages.showing') }} {{ $products->firstItem() }}–{{ $products->lastItem() }} {{ __('messages.of') }} {{ $products->total() }} {{ __('messages.products') }}
        </div>
        <div class="pagination">
            {{ $products->links() }}
        </div>
    </div>
    @endif

    @else
    {{-- Empty State --}}
    <div class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V11"/>
        </svg>
        <h3 class="empty-state-title">{{ __('messages.no_products_found') }}</h3>
        <p class="empty-state-description">
            @if(array_filter($filters))
                {{ __('messages.no_products_match') }}
            @else
                {{ __('messages.add_first_product') }}
            @endif
        </p>
        <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('messages.add_product') }}
            </a>
            @if(array_filter($filters))
                <a href="{{ route('products.index') }}" class="btn btn-secondary">{{ __('messages.clear_filters') }}</a>
            @endif
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Select-all checkbox logic
    const selectAllTh  = document.getElementById('select-all-th');
    const checkboxes   = document.querySelectorAll('.product-checkbox');
    const selectedCount = document.getElementById('selected-count');
    const bulkPublish   = document.getElementById('bulk-publish-btn');
    const bulkDraft     = document.getElementById('bulk-draft-btn');
    const bulkDelete    = document.getElementById('bulk-delete-btn');

    function updateBulkBar() {
        const checked = document.querySelectorAll('.product-checkbox:checked');
        const count   = checked.length;
        if (selectedCount) selectedCount.textContent = count > 0 ? `${count} selected` : '';
        [bulkPublish, bulkDraft, bulkDelete].forEach(btn => { if(btn) btn.disabled = count === 0; });
        if (selectAllTh) selectAllTh.indeterminate = count > 0 && count < checkboxes.length;
        if (selectAllTh) selectAllTh.checked = count === checkboxes.length && count > 0;
    }

    if (selectAllTh) {
        selectAllTh.addEventListener('change', () => {
            checkboxes.forEach(cb => cb.checked = selectAllTh.checked);
            updateBulkBar();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));

    // Auto-focus search
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && searchInput.value) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
    });
</script>
@endpush

@endsection
