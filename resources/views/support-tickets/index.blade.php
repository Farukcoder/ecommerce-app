@extends('tyro-dashboard::layouts.user')

@section('title', 'Support & Communication')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Support & Communication</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Support & Communication</h1>
            <p class="page-description">Messages submitted from the website contact form.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Open</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['open'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">In Progress</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['in_progress'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Resolved</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['resolved'] }}</div>
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
        <form action="{{ route('support-tickets.index') }}" method="GET">
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="form-input" placeholder="Search ticket #, name, email, phone, order" value="{{ $filters['search'] ?? '' }}">
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
                    <label class="filter-label">Subject:</label>
                    <select name="subject" class="form-select" style="min-width: 160px;">
                        <option value="">All</option>
                        @foreach($subjects as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['subject'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                    <a href="{{ route('support-tickets.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    @if($tickets->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Contact</th>
                    <th>Subject</th>
                    <th>Order</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $ticket)
                    @php
                        $statusColors = [
                            'open' => 'badge-warning',
                            'in_progress' => 'badge-primary',
                            'resolved' => 'badge-success',
                            'closed' => 'badge-secondary',
                        ];
                    @endphp
                    <tr>
                        <td style="font-weight:500;">{{ $ticket->ticket_number }}</td>
                        <td>
                            <div style="font-weight:500;">{{ $ticket->name }}</div>
                            <div style="font-size:0.8125rem; color:var(--muted-foreground);">{{ $ticket->email }}</div>
                            <div style="font-size:0.8125rem; color:var(--muted-foreground);">{{ $ticket->phone }}</div>
                        </td>
                        <td>{{ $ticket->subject_label }}</td>
                        <td>{{ $ticket->order_number ?: '—' }}</td>
                        <td>{{ $ticket->created_at?->format('M d, Y h:i A') }}</td>
                        <td>
                            <span class="badge {{ $statusColors[$ticket->status] ?? 'badge-secondary' }}">
                                {{ $ticket->status_label }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('support-tickets.show', $ticket) }}" class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer" style="padding: 1rem 1.5rem;">
        {{ $tickets->links() }}
    </div>
    @else
    <div class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        <h3 class="empty-state-title">No support tickets yet</h3>
        <p class="empty-state-description">
            @if(array_filter($filters))
                No tickets match your filters.
            @else
                Tickets from the website contact form will appear here.
            @endif
        </p>
        @if(array_filter($filters))
            <a href="{{ route('support-tickets.index') }}" class="btn btn-secondary">Clear Filters</a>
        @endif
    </div>
    @endif
</div>
@endsection
