@extends('tyro-dashboard::layouts.user')

@section('title', 'Customers')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Customers</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Customers</h1>
            <p class="page-description">Browse customer accounts and their order activity.</p>
        </div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Customers</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['total_customers'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">With Orders</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['with_orders'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">New This Month</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['new_this_month'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Spent</div>
            <div class="stat-value" style="font-size:1.5rem;">@money($summary['total_spent'])</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <form action="{{ route('customers.index') }}" method="GET">
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="form-input" placeholder="{{ __('messages.search_customers') }}" value="{{ $filters['search'] ?? '' }}">
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(array_filter($filters))
                    <a href="{{ route('customers.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    @if($customers->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Last Order</th>
                    <th>Joined</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                <tr>
                    <td style="font-weight:500;">
                        <a href="{{ route('customers.show', $customer) }}" style="color:var(--primary); text-decoration:none; font-weight:600;">{{ $customer->name }}</a>
                    </td>
                    <td>{{ $customer->email }}</td>
                    <td>
                        <span class="badge badge-primary">{{ $customer->orders_count }}</span>
                    </td>
                    <td style="font-weight:600;">@money((float) ($customer->orders_sum_total_amount ?? 0))</td>
                    <td>
                        @if($customer->orders_max_created_at)
                            {{ \Illuminate\Support\Carbon::parse($customer->orders_max_created_at)->format('M d, Y') }}
                        @else
                                <span style="color:var(--muted-foreground);">-</span>
                        @endif
                    </td>
                    <td>{{ $customer->created_at?->format('M d, Y') }}</td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex; gap:0.375rem; justify-content:flex-end;">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-primary">View Profile</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
        <div style="font-size:0.875rem; color:var(--muted-foreground);">
                Showing {{ $customers->firstItem() }}-{{ $customers->lastItem() }} of {{ $customers->total() }} customers
        </div>
        <div class="pagination">
            {{ $customers->links() }}
        </div>
    </div>
    @endif
    @else
    <div class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        <h3 class="empty-state-title">No customers found</h3>
        <p class="empty-state-description">
            @if(array_filter($filters))
                No customers match your search.
            @else
                Customers will appear here once accounts are created.
            @endif
        </p>
        @if(array_filter($filters))
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Clear Search</a>
        @endif
    </div>
    @endif
</div>
@endsection
