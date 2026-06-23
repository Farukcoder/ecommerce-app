@extends('tyro-dashboard::layouts.user')

@section('title', 'Attributes')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Attributes</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Attributes</h1>
            <p class="page-description">Manage product attributes such as Size, Color, Material and their values.</p>
        </div>
        <a href="{{ route('attributes.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Attribute
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <form action="{{ route('attributes.index') }}" method="GET">
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="form-input" placeholder="{{ __('messages.search_attributes') }}" value="{{ $filters['search'] ?? '' }}">
                </div>
                <button type="submit" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filter
                </button>
                @if(array_filter($filters))
                    <a href="{{ route('attributes.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Attributes Table --}}
<div class="card">
    @if($attributes->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Values</th>
                    <th style="width:100px;">Total</th>
                    <th style="width:160px;">Created</th>
                    <th style="text-align:right; width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attributes as $attribute)
                <tr>
                    <td>
                        <div style="font-weight:500; font-size:0.9375rem; color:var(--foreground);">{{ $attribute->name }}</div>
                    </td>
                    <td>
                        <div style="display:flex; flex-wrap:wrap; gap:4px; max-width:480px;">
                            @forelse($attribute->values->take(8) as $val)
                                <span class="badge badge-secondary">{{ $val->value }}</span>
                            @empty
                                <span style="color:var(--muted-foreground); font-size:0.8125rem;">No values yet</span>
                            @endforelse
                            @if($attribute->values->count() > 8)
                                <span class="badge badge-secondary">+{{ $attribute->values->count() - 8 }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-primary">{{ $attribute->values_count }}</span>
                    </td>
                    <td>
                        <span style="font-size:0.8125rem; color:var(--muted-foreground);">
                            {{ $attribute->created_at->format('M d, Y') }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content:flex-end;">
                            <a href="{{ route('attributes.edit', $attribute) }}" class="action-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('attributes.destroy', $attribute->id) }}" method="POST" style="display:inline;" id="delete-attribute-{{ $attribute->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="action-btn action-btn-danger" title="Delete"
                                    onclick="showDanger('Delete Attribute', 'Are you sure you want to delete &quot;{{ addslashes($attribute->name) }}&quot;? All its values will also be removed.').then(confirmed => { if(confirmed) document.getElementById('delete-attribute-{{ $attribute->id }}').submit(); })">
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
    @if($attributes->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
        <div style="font-size:0.875rem; color:var(--muted-foreground);">
            Showing {{ $attributes->firstItem() }}–{{ $attributes->lastItem() }} of {{ $attributes->total() }} attributes
        </div>
        <div class="pagination">
            {{ $attributes->links() }}
        </div>
    </div>
    @endif

    @else
    {{-- Empty State --}}
    <div class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        <h3 class="empty-state-title">No attributes found</h3>
        <p class="empty-state-description">
            @if(array_filter($filters))
                No attributes match your search.
            @else
                Create attributes like Size or Color so products can have variants.
            @endif
        </p>
        <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('attributes.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Attribute
            </a>
            @if(array_filter($filters))
                <a href="{{ route('attributes.index') }}" class="btn btn-secondary">Clear Search</a>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
