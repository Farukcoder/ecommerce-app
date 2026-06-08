@extends('tyro-dashboard::layouts.user')

@section('title', 'Dashboard')

@section('breadcrumb')
<span>Dashboard</span>
@endsection

@section('content')
@php
    $totalRevenue = (float) ($stats['total_revenue'] ?? 0);
    $totalOrders = (int) ($stats['total_orders'] ?? 0);
    $totalProducts = (int) ($stats['total_products'] ?? 0);
    $totalCustomers = (int) ($stats['total_customers'] ?? 0);
    $pendingOrders = (int) ($stats['pending_orders'] ?? 0);
    $availableBalance = (float) ($stats['available_balance'] ?? 0);
    
    $monthGrowthPct = (float) ($stats['month_growth_pct'] ?? 0);
    $weeklySales = collect($stats['weekly_sales'] ?? []);
    $monthlySales = collect($stats['monthly_sales'] ?? []);
    $recentOrders = collect($stats['recent_orders'] ?? []);
    
    $topSelling = collect($stats['top_selling'] ?? []);
    $lowStock = collect($stats['low_stock'] ?? []);
    $latestReviews = collect($stats['latest_reviews'] ?? []);
    $recentTransactions = collect($stats['recent_transactions'] ?? []);
@endphp

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Store Overview</h1>
            <p class="page-description" style="font-size: 1rem; color: var(--muted-foreground);">
                Manage your store operations, analyze key indicators, and review latest activity.
            </p>
        </div>
    </div>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
    <!-- Total Revenue -->
    <div class="stat-card" style="display: flex; gap: 1rem; align-items: center; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: 1px solid var(--border); background: var(--card); padding: 1.25rem;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='none';">
        <div class="stat-icon stat-icon-success" style="background: hsla(142, 70%, 45%, 0.1); color: hsl(142, 70%, 45%); padding: 0.75rem; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10V6m0 12v-2m6-6a6 6 0 11-12 0 6 6 0 0112 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.875rem; color: var(--muted-foreground); font-weight: 500;">Total Revenue</div>
            <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; color: var(--foreground);">${{ number_format($totalRevenue, 2) }}</div>
            <div class="stat-change {{ $monthGrowthPct >= 0 ? 'stat-change-up' : 'stat-change-down' }}" style="font-size: 0.8125rem; display: flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $monthGrowthPct >= 0 ? 'M7 17l9-9m0 0H7m9 0v9' : 'M7 7l9 9m0 0V7m0 9H7' }}"></path>
                </svg>
                <span>{{ $monthGrowthPct >= 0 ? '+' : '' }}{{ number_format($monthGrowthPct, 1) }}% vs last month</span>
            </div>
        </div>
    </div>

    <!-- Available Balance -->
    <div class="stat-card" style="display: flex; gap: 1rem; align-items: center; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: 1px solid var(--border); background: var(--card); padding: 1.25rem;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='none';">
        <div class="stat-icon stat-icon-primary" style="background: hsla(220, 90%, 56%, 0.1); color: hsl(220, 90%, 56%); padding: 0.75rem; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.875rem; color: var(--muted-foreground); font-weight: 500;">Available Balance</div>
            <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; color: var(--foreground);">${{ number_format($availableBalance, 2) }}</div>
            <span style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 0.25rem;">Settled funds</span>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="stat-card" style="display: flex; gap: 1rem; align-items: center; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: 1px solid var(--border); background: var(--card); padding: 1.25rem;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='none';">
        <div class="stat-icon stat-icon-info" style="background: hsla(190, 80%, 45%, 0.1); color: hsl(190, 80%, 45%); padding: 0.75rem; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.875rem; color: var(--muted-foreground); font-weight: 500;">Total Orders</div>
            <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; color: var(--foreground);">{{ number_format($totalOrders) }}</div>
            <span style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 0.25rem;">Lifetime volume</span>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="stat-card" style="display: flex; gap: 1rem; align-items: center; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: 1px solid var(--border); background: var(--card); padding: 1.25rem;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='none';">
        <div class="stat-icon stat-icon-warning" style="background: hsla(35, 90%, 50%, 0.1); color: hsl(35, 90%, 50%); padding: 0.75rem; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.875rem; color: var(--muted-foreground); font-weight: 500;">Pending Orders</div>
            <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; color: var(--foreground);">{{ number_format($pendingOrders) }}</div>
            <span style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 0.25rem;">Awaiting processing</span>
        </div>
    </div>

    <!-- Products -->
    <div class="stat-card" style="display: flex; gap: 1rem; align-items: center; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: 1px solid var(--border); background: var(--card); padding: 1.25rem;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='none';">
        <div class="stat-icon stat-icon-primary" style="background: hsla(260, 80%, 60%, 0.1); color: hsl(260, 80%, 60%); padding: 0.75rem; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.875rem; color: var(--muted-foreground); font-weight: 500;">Products</div>
            <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; color: var(--foreground);">{{ number_format($totalProducts) }}</div>
            <span style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 0.25rem;">Catalog size</span>
        </div>
    </div>

    <!-- Customers -->
    <div class="stat-card" style="display: flex; gap: 1rem; align-items: center; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: 1px solid var(--border); background: var(--card); padding: 1.25rem;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='none';">
        <div class="stat-icon stat-icon-info" style="background: hsla(170, 75%, 40%, 0.1); color: hsl(170, 75%, 40%); padding: 0.75rem; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.875rem; color: var(--muted-foreground); font-weight: 500;">Customers</div>
            <div class="stat-value" style="font-size: 1.5rem; font-weight: 700; color: var(--foreground);">{{ number_format($totalCustomers) }}</div>
            <span style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 0.25rem;">Registered buyers</span>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    <!-- Sales Chart (Monthly) -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="font-size: 1.0625rem;">Sales Chart (Monthly)</h3>
            <span class="badge badge-secondary">6-Month Trend</span>
        </div>
        <div class="card-body">
            <div style="display:flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.875rem; color: var(--muted-foreground);">Total Revenue (6 Months)</div>
                    <div style="font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em;">${{ number_format($monthlySales->sum('total'), 2) }}</div>
                </div>
                <div class="badge-list">
                    <span class="badge badge-primary">{{ count($monthlySales) }} months</span>
                </div>
            </div>

            <div style="border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; background: var(--muted);">
                <div style="display:grid; grid-template-columns: repeat(6, 1fr); gap: 0.75rem; align-items: end; height: 180px;">
                    @forelse($monthlySales as $month)
                        <div style="display:flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                            <div title="{{ $month['label'] }}: {{ $month['formatted_total'] }} ({{ $month['order_count'] }} orders)" style="height: 150px; display:flex; align-items:flex-end; position: relative; cursor: pointer;">
                                <div style="position:absolute; top: -18px; left: 0; right: 0; text-align:center; font-size: 0.75rem; font-weight: 600; color: var(--foreground);">{{ $month['formatted_total'] }}</div>
                                <div style="width: 100%; height: {{ $month['height'] }}%; background: var(--foreground); border-radius: 6px; border: 1px solid var(--border); transition: filter 0.2s;" onmouseover="this.style.filter='brightness(1.2)';" onmouseout="this.style.filter='none';"></div>
                            </div>
                            <div style="font-size: 0.8125rem; font-weight: 600; color: var(--muted-foreground); text-align:center;">{{ $month['label'] }}</div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; display:flex; align-items:center; justify-content:center; min-height: 150px; color: var(--muted-foreground);">
                            No sales data available.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="font-size: 1.0625rem;">Recent Orders</h3>
            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-ghost" style="font-size: 0.8125rem;">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($recentOrders as $order)
                <div style="display:flex; justify-content: space-between; gap: 1rem; padding: 0.9rem 1rem; border-bottom: 1px solid var(--border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--muted)';" onmouseout="this.style.backgroundColor='transparent';">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-weight: 600; color: var(--foreground); letter-spacing: -0.01em;">{{ $order['order_number'] }}</span>
                            <span style="font-size: 0.8125rem; color: var(--muted-foreground);">by {{ $order['customer_name'] }}</span>
                        </div>
                        <div style="font-size: 0.875rem; color: var(--muted-foreground); margin-top: 0.25rem;">
                            {{ $order['placed_at'] }} · {{ $order['item_count'] }} item{{ $order['item_count'] === 1 ? '' : 's' }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700;">{{ $order['formatted_total'] }}</div>
                        <div class="badge-list" style="justify-content: flex-end; margin-top: 0.35rem; gap: 0.25rem;">
                            <span class="badge {{ $order['status_class'] }}">{{ $order['status_label'] }}</span>
                            <span class="badge {{ $order['payment_class'] }}">{{ $order['payment_label'] }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="padding: 2rem;">
                    <p class="empty-state-description">No orders placed yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    <!-- Top Selling Products -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="font-size: 1.0625rem;">Top Selling Products</h3>
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-ghost" style="font-size: 0.8125rem;">View Catalog</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($topSelling as $product)
                <div style="display:flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.75rem 1rem; border-bottom: 1px solid var(--border);">
                    <div style="display:flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 6px; overflow: hidden; background: var(--muted); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            @if($product['image'])
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: var(--muted-foreground);">
                                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9375rem; color: var(--foreground);">{{ $product['name'] }}</div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground);">SKU: {{ $product['sku'] }}</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700; font-size: 0.9375rem;">{{ $product['formatted_revenue'] }}</div>
                        <div style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 0.15rem;">{{ $product['total_qty'] }} sold</div>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="padding: 2rem;">
                    <p class="empty-state-description">No sales data available yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title" style="font-size: 1.0625rem; color: hsl(35, 90%, 50%); display: flex; align-items: center; gap: 0.5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Low Stock Alerts
            </h3>
            <span class="badge badge-danger">Attention Required</span>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($lowStock as $product)
                <div style="display:flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.75rem 1rem; border-bottom: 1px solid var(--border);">
                    <div style="display:flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 6px; overflow: hidden; background: var(--muted); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            @if($product['image'])
                                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: var(--muted-foreground);">
                                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9375rem; color: var(--foreground);">{{ $product['name'] }}</div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground);">SKU: {{ $product['sku'] }}</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge {{ $product['status_class'] }}" style="font-weight: 600;">{{ $product['qty'] }} left</span>
                        <div style="font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 0.25rem;">{{ $product['status'] }}</div>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="padding: 2rem;">
                    <p class="empty-state-description" style="color: var(--success);">All products are well stocked!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">
    <!-- Latest Reviews -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Latest Reviews</h3>
        </div>
        <div class="card-body" style="padding: 0.5rem 1rem;">
            @forelse($latestReviews as $review)
                <div style="padding: 1rem 0; border-bottom: 1px solid var(--border); display: flex; gap: 0.875rem;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--foreground); color: var(--background); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; flex-shrink: 0;">
                        {{ $review['avatar_letter'] }}
                    </div>
                    <div style="flex-grow: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 0.5rem;">
                            <div style="font-weight: 600; font-size: 0.9375rem; color: var(--foreground);">{{ $review['customer_name'] }}</div>
                            <span style="font-size: 0.8125rem; color: var(--muted-foreground);">{{ $review['time_ago'] }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                            <!-- Star Rating -->
                            <div style="display: flex; color: hsl(45, 100%, 50%); gap: 1px;">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg viewBox="0 0 24 24" fill="{{ $i <= $review['rating'] ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.519-4.674z" />
                                    </svg>
                                @endfor
                            </div>
                            <span style="font-size: 0.8125rem; color: var(--muted-foreground);">on <strong style="color: var(--foreground);">{{ $review['product_name'] }}</strong></span>
                        </div>
                        <p style="font-size: 0.875rem; color: var(--muted-foreground); margin: 0.5rem 0 0 0; line-height: 1.4;">
                            "{{ $review['comment'] }}"
                        </p>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="padding: 2rem;">
                    <p class="empty-state-description">No reviews yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Recent Transactions</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container" style="border: none;">
                <table class="table" style="font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <th style="padding: 0.75rem 1rem; text-align: left; color: var(--muted-foreground); font-weight: 500;">Txn ID</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; color: var(--muted-foreground); font-weight: 500;">Customer</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; color: var(--muted-foreground); font-weight: 500;">Method</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; color: var(--muted-foreground); font-weight: 500;">Amount</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; color: var(--muted-foreground); font-weight: 500;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $txn)
                            <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--muted)';" onmouseout="this.style.backgroundColor='transparent';">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; font-family: monospace; color: var(--foreground);">{{ $txn['transaction_id'] }}</td>
                                <td style="padding: 0.75rem 1rem; color: var(--muted-foreground);">
                                    <div style="font-weight: 500; color: var(--foreground);">{{ $txn['customer_name'] }}</div>
                                    <div style="font-size: 0.75rem; color: var(--muted-foreground);">Order: {{ $txn['order_number'] }}</div>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <span class="badge badge-secondary" style="font-size: 0.75rem; padding: 0.15rem 0.4rem;">{{ $txn['payment_method'] }}</span>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: var(--foreground);">{{ $txn['formatted_amount'] }}</td>
                                <td style="padding: 0.75rem 1rem; text-align: right;">
                                    <span class="badge {{ $txn['status_class'] }}" style="font-size: 0.75rem;">{{ $txn['status'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: var(--muted-foreground);">
                                    No transactions recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
