@extends('tyro-dashboard::layouts.user')

@section('title', 'Product Reviews')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Product Reviews</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Product Reviews</h1>
            <p class="page-description">Moderate and check feedback from your customers.</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <form action="{{ route('reviews.index') }}" method="GET">
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="form-input" placeholder="Search reviews by customer, product, comment…" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Rating:</label>
                    <select name="rating" class="form-select" style="min-width: 140px;">
                        <option value="">All Ratings</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ ($filters['rating'] ?? '') == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filter
                </button>
                @if(array_filter($filters, fn($v) => $v !== null && $v !== ''))
                    <a href="{{ route('reviews.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Reviews Table --}}
<div class="card">
    @if($reviews->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:200px;">Customer</th>
                    <th style="width:240px;">Product</th>
                    <th style="width:130px;">Rating</th>
                    <th>Comment</th>
                    <th style="width:140px;">Submitted At</th>
                    <th style="text-align:right; width:80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $review)
                <tr>
                    <td>
                        <div style="font-weight:600; font-size:0.9375rem; color:var(--foreground);">{{ $review->user ? $review->user->name : 'Unknown User' }}</div>
                        <div style="font-size:0.8125rem; color:var(--muted-foreground);">{{ $review->user ? $review->user->email : '' }}</div>
                    </td>
                    <td>
                        @if($review->product)
                            <div style="display:flex; align-items:center; gap:0.625rem;">
                                <div style="width:40px; height:40px; border-radius:6px; background:var(--muted); border:1px solid var(--border); flex-shrink:0; display:flex; align-items:center; justify-content:center; color:var(--muted-foreground); overflow:hidden;">
                                    @if($review->product->thumbnail)
                                        <img src="{{ asset('storage/'.$review->product->thumbnail) }}" alt="{{ $review->product->name }}" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:18px;height:18px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div style="font-weight:500; font-size:0.875rem; color:var(--foreground); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px;">
                                    {{ $review->product->name }}
                                </div>
                            </div>
                        @else
                            <span style="color:var(--muted-foreground); font-size:0.875rem;">Deleted Product</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:2px;">
                            @for($i = 1; $i <= 5; $i++)
                                <svg viewBox="0 0 24 24" fill="{{ $i <= $review->rating ? 'var(--warning, #f59e0b)' : 'none' }}" stroke="{{ $i <= $review->rating ? 'var(--warning, #f59e0b)' : 'var(--muted-foreground)' }}" stroke-width="2" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z"/>
                                </svg>
                            @endfor
                        </div>
                    </td>
                    <td>
                        <div style="font-size:0.875rem; color:var(--foreground); line-height:1.4; word-break:break-word; max-width:400px;">
                            {{ $review->comment ?: '—' }}
                        </div>
                    </td>
                    <td>
                        <span style="font-size:0.8125rem; color:var(--muted-foreground);">
                            {{ $review->created_at->format('M d, Y h:i A') }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons" style="justify-content:flex-end;">
                            <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" style="display:inline;" id="delete-review-{{ $review->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="action-btn action-btn-danger" title="Delete Review"
                                    onclick="showDanger('Delete Review', 'Are you sure you want to delete this review? This action cannot be undone.').then(confirmed => { if(confirmed) document.getElementById('delete-review-{{ $review->id }}').submit(); })">
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
    @if($reviews->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
        <div style="font-size:0.875rem; color:var(--muted-foreground);">
            Showing {{ $reviews->firstItem() }}–{{ $reviews->lastItem() }} of {{ $reviews->total() }} reviews
        </div>
        <div class="pagination">
            {{ $reviews->links() }}
        </div>
    </div>
    @endif

    @else
    {{-- Empty State --}}
    <div class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z"/>
        </svg>
        <h3 class="empty-state-title">No reviews found</h3>
        <p class="empty-state-description">
            @if(array_filter($filters, fn($v) => $v !== null && $v !== ''))
                No reviews match your current filters.
            @else
                Product reviews from your customers will show up here.
            @endif
        </p>
        @if(array_filter($filters, fn($v) => $v !== null && $v !== ''))
            <div style="display:flex; justify-content:center; margin-top:0.75rem;">
                <a href="{{ route('reviews.index') }}" class="btn btn-secondary">Clear Filters</a>
            </div>
        @endif
    </div>
    @endif
</div>

@endsection
