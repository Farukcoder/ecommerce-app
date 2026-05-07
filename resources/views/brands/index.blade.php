@extends('tyro-dashboard::layouts.user')

@section('title', 'Brands')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Brands</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Brands</h1>
            <p class="page-description">Manage product brands, logos and visibility.</p>
        </div>
        <a href="{{ route('brands.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Brand
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <form action="{{ route('brands.index') }}" method="GET">
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="form-input" placeholder="Search brands by name or slug…" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status:</label>
                    <select name="status" class="form-select" style="min-width: 140px;">
                        <option value="">All</option>
                        <option value="1" {{ ($filters['status'] ?? '') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($filters['status'] ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filter
                </button>
                @if(array_filter($filters, fn($v) => $v !== null && $v !== ''))
                    <a href="{{ route('brands.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Brands Table --}}
<div class="card">
    @if($brands->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:80px;">Logo</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th style="width:120px;">Products</th>
                    <th style="width:120px;">Status</th>
                    <th style="width:160px;">Created</th>
                    <th style="text-align:right; width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                <tr>
                    <td>
                        <div style="width:44px; height:44px; border-radius:8px; background:var(--muted); border:1px solid var(--border); flex-shrink:0; display:flex; align-items:center; justify-content:center; color:var(--muted-foreground); overflow:hidden;">
                            @if($brand->logo)
                                <img src="{{ asset('storage/'.$brand->logo) }}" alt="{{ $brand->name }}" style="width:100%; height:100%; object-fit:contain; padding:4px;">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:500; font-size:0.9375rem; color:var(--foreground);">{{ $brand->name }}</div>
                    </td>
                    <td>
                        <code style="font-size:0.8125rem; background:var(--muted); padding:2px 6px; border-radius:4px; color:var(--muted-foreground);">{{ $brand->slug }}</code>
                    </td>
                    <td>
                        <span class="badge badge-primary">{{ $brand->products_count }}</span>
                    </td>
                    <td>
                        @if($brand->status)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:0.8125rem; color:var(--muted-foreground);">
                            {{ $brand->created_at->format('M d, Y') }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content:flex-end;">
                            <a href="{{ route('brands.edit', $brand) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('brands.destroy', $brand->id) }}" method="POST" style="display:inline;" id="delete-brand-{{ $brand->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="action-btn action-btn-danger" title="Delete"
                                    onclick="showDanger('Delete Brand', 'Are you sure you want to delete &quot;{{ addslashes($brand->name) }}&quot;? Products linked to this brand will keep their data but lose the brand reference.').then(confirmed => { if(confirmed) document.getElementById('delete-brand-{{ $brand->id }}').submit(); })">
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
    @if($brands->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
        <div style="font-size:0.875rem; color:var(--muted-foreground);">
            Showing {{ $brands->firstItem() }}–{{ $brands->lastItem() }} of {{ $brands->total() }} brands
        </div>
        <div class="pagination">
            {{ $brands->links() }}
        </div>
    </div>
    @endif

    @else
    {{-- Empty State --}}
    <div class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
        </svg>
        <h3 class="empty-state-title">No brands found</h3>
        <p class="empty-state-description">
            @if(array_filter($filters, fn($v) => $v !== null && $v !== ''))
                No brands match your current filters.
            @else
                Get started by adding the first brand to your catalog.
            @endif
        </p>
        <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('brands.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Brand
            </a>
            @if(array_filter($filters, fn($v) => $v !== null && $v !== ''))
                <a href="{{ route('brands.index') }}" class="btn btn-secondary">Clear Filters</a>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
