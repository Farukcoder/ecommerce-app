@extends('tyro-dashboard::layouts.user')

@section('title', 'Dashboard')

@section('breadcrumb')
<span>Dashboard</span>
@endsection

@section('content')
@php
    $totalOrders = (int) ($stats['total_orders'] ?? 0);
    $totalSpent = (float) ($stats['total_spent'] ?? 0);
    $pendingOrders = (int) ($stats['pending_orders'] ?? 0);
    $fulfilledOrders = (int) ($stats['fulfilled_orders'] ?? 0);
    $refundedOrders = (int) ($stats['refunded_orders'] ?? 0);
    $averageOrderValue = (float) ($stats['average_order_value'] ?? 0);
    $monthGrowthPct = (float) ($stats['month_growth_pct'] ?? 0);
    $weeklySales = collect($stats['weekly_sales'] ?? []);
    $recentOrders = collect($stats['recent_orders'] ?? []);
@endphp

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Welcome back, {{ $user->name ?? 'User' }}!</h1>
            <p class="page-description" style="font-size: 1rem;">
                You have {{ number_format($totalOrders) }} orders, {{ number_format($pendingOrders) }} still pending, and ${{ number_format($totalSpent, 2) }} in lifetime spend.
            </p>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6a2 2 0 012 2v14H7V5a2 2 0 012-2z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Total Orders</div>
            <div class="stat-value">{{ number_format($totalOrders) }}</div>
            <div class="stat-change stat-change-up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17l9-9m0 0H7m9 0v9"></path></svg>
                <span>{{ number_format($fulfilledOrders) }} fulfilled</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Lifetime Spend</div>
            <div class="stat-value">${{ number_format($totalSpent, 2) }}</div>
            <div class="stat-change {{ $monthGrowthPct >= 0 ? 'stat-change-up' : 'stat-change-down' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $monthGrowthPct >= 0 ? 'M7 17l9-9m0 0H7m9 0v9' : 'M7 7l9 9m0 0V7m0 9H7' }}"></path>
                </svg>
                <span>{{ $monthGrowthPct >= 0 ? '+' : '' }}{{ number_format($monthGrowthPct, 1) }}% vs last month</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"></path><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-9-9"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Pending Orders</div>
            <div class="stat-value">{{ number_format($pendingOrders) }}</div>
            <div class="stat-change stat-change-down">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l9 9m0 0V7m0 9H7"></path></svg>
                <span>{{ number_format($refundedOrders) }} refunded</span>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16"></path><path stroke-linecap="round" stroke-linejoin="round" d="M6 17V7m6 10V5m6 12v-8"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Average Order Value</div>
            <div class="stat-value">${{ number_format($averageOrderValue, 2) }}</div>
            <div class="stat-change stat-change-up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17l9-9m0 0H7m9 0v9"></path></svg>
                <span>{{ number_format($recentOrders->count()) }} recent orders</span>
            </div>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Weekly Spend</h3>
            <span class="badge badge-secondary">7-day chart</span>
        </div>
        <div class="card-body">
            <div style="display:flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.875rem; color: var(--muted-foreground);">Total</div>
                    <div style="font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em;">${{ number_format(collect($weeklySales)->sum('total'), 2) }}</div>
                </div>
                <div class="badge-list">
                    <span class="badge badge-primary">{{ count($weeklySales) }} days</span>
                </div>
            </div>

            <div style="border: 1px solid var(--border); border-radius: 10px; padding: 1rem; background: var(--muted);">
                <div style="display:grid; grid-template-columns: repeat(7, 1fr); gap: 0.625rem; align-items: end; height: 180px;">
                    @forelse($weeklySales as $day)
                        <div style="display:flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                            <div title="{{ $day['label'] }}: {{ $day['formatted_total'] }}" style="height: 150px; display:flex; align-items:flex-end; position: relative;">
                                <div style="position:absolute; top: 0; left: 0; right: 0; text-align:center; font-size: 0.75rem; color: var(--muted-foreground);">{{ $day['formatted_total'] }}</div>
                                <div style="width: 100%; height: {{ $day['height'] }}%; background: var(--foreground); border-radius: 8px; border: 1px solid var(--border);"></div>
                            </div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground); text-align:center;">{{ $day['label'] }}</div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; display:flex; align-items:center; justify-content:center; min-height: 150px; color: var(--muted-foreground);">
                            No orders yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Recent Orders</h3>
            <span class="badge badge-secondary">Latest activity</span>
        </div>
        <div class="card-body">
            @forelse($recentOrders as $order)
                <div style="display:flex; justify-content: space-between; gap: 1rem; padding: 0.9rem 0; border-bottom: 1px solid var(--border);">
                    <div>
                        <div style="font-weight: 600; letter-spacing: -0.01em;">{{ $order['order_number'] }}</div>
                        <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-top: 0.25rem;">
                            {{ $order['placed_at'] }} · {{ $order['item_count'] }} item{{ $order['item_count'] === 1 ? '' : 's' }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700;">{{ $order['formatted_total'] }}</div>
                        <div class="badge-list" style="justify-content: flex-end; margin-top: 0.35rem;">
                            <span class="badge {{ $order['status_class'] }}">{{ $order['status_label'] }}</span>
                            <span class="badge {{ $order['payment_class'] }}">{{ $order['payment_label'] }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p class="empty-state-description">You have not placed any orders yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
