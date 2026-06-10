@extends('tyro-dashboard::layouts.user')

@section('title', 'Contact Us')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Contact Us</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Contact Us</h1>
            <p class="page-description">General inquiries submitted from the website contact form.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">New</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['new'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Read</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['read'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Replied</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['replied'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Today</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['today'] }}</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <form action="{{ route('contact-us.index') }}" method="GET">
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="form-input" placeholder="Search name, email, phone, message" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status:</label>
                    <select name="status" class="form-select" style="min-width: 140px;">
                        <option value="">All</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">From:</label>
                    <input type="date" name="date_from" class="form-input" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="filter-group">
                    <label class="filter-label">To:</label>
                    <input type="date" name="date_to" class="form-input" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(array_filter($filters))
                    <a href="{{ route('contact-us.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    @if($messages->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($messages as $item)
                    @php
                        $statusColors = [
                            'new' => 'badge-warning',
                            'read' => 'badge-primary',
                            'replied' => 'badge-success',
                            'archived' => 'badge-secondary',
                        ];
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:500;">{{ $item->name }}</div>
                            <div style="font-size:0.8125rem; color:var(--muted-foreground);">{{ $item->email }}</div>
                            <div style="font-size:0.8125rem; color:var(--muted-foreground);">{{ $item->phone }}</div>
                        </td>
                        <td style="max-width:320px;">
                            <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $item->message }}</div>
                        </td>
                        <td>{{ $item->created_at?->format('M d, Y h:i A') }}</td>
                        <td>
                            <span class="badge {{ $statusColors[$item->status] ?? 'badge-secondary' }}">
                                {{ $item->status_label }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('contact-us.show', $item) }}" class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer" style="padding: 1rem 1.5rem;">
        {{ $messages->links() }}
    </div>
    @else
    <div class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h3 class="empty-state-title">No contact messages yet</h3>
        <p class="empty-state-description">
            @if(array_filter($filters))
                No messages match your filters.
            @else
                Messages from the website contact form will appear here.
            @endif
        </p>
        @if(array_filter($filters))
            <a href="{{ route('contact-us.index') }}" class="btn btn-secondary">Clear Filters</a>
        @endif
    </div>
    @endif
</div>
@endsection
