@extends('tyro-dashboard::layouts.user')

@section('title', 'Customer Details - ' . $customer->name)

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('customers.index') }}">Customers</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $customer->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $customer->name }}</h1>
            <p class="page-description">Customer Profile & Order History</p>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <a href="{{ route('customers.index') }}" class="btn btn-ghost">Back to Customers</a>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 1.5rem; align-items:start;">
    <div>
        <!-- Customer Info Card -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Customer Information</h3>
            </div>
            <div class="card-body">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1.25rem;">
                    <div>
                        <div class="form-hint" style="font-size:0.8125rem; color:var(--muted-foreground); margin-bottom:0.25rem;">Full Name</div>
                        <div style="font-weight:600; font-size:1rem;">{{ $customer->name }}</div>
                    </div>
                    <div>
                        <div class="form-hint" style="font-size:0.8125rem; color:var(--muted-foreground); margin-bottom:0.25rem;">Email Address</div>
                        <div style="font-size:1rem;"><a href="mailto:{{ $customer->email }}" style="color:var(--primary); text-decoration:none;">{{ $customer->email }}</a></div>
                    </div>
                    <div>
                        <div class="form-hint" style="font-size:0.8125rem; color:var(--muted-foreground); margin-bottom:0.25rem;">Joined Date</div>
                        <div style="font-size:1rem;">{{ $customer->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="form-hint" style="font-size:0.8125rem; color:var(--muted-foreground); margin-bottom:0.25rem;">Account Status</div>
                        <div>
                            <span class="badge badge-success">Active Customer</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order History Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Order History</h3>
                <span class="badge badge-secondary">{{ $customer->orders->count() }} orders</span>
            </div>
            @if($customer->orders->count())
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->orders as $order)
                                <tr>
                                    <td style="font-weight:600;">{{ $order->order_number }}</td>
                                    <td>{{ $order->created_at?->format('M d, Y') }}</td>
                                    <td>{{ $order->items_count }}</td>
                                    <td style="font-weight:600;">@money($order->total_amount)</td>
                                    <td>
                                        <div>{{ strtoupper($order->payment_method ?? 'N/A') }}</div>
                                        <div style="font-size:0.8125rem; color:var(--muted-foreground);">{{ ucfirst($order->payment_status) }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'badge-warning',
                                                'processing' => 'badge-primary',
                                                'shipped' => 'badge-secondary',
                                                'delivered' => 'badge-success',
                                                'cancelled' => 'badge-danger',
                                                'refunded' => 'badge-secondary',
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusColors[$order->status] ?? 'badge-secondary' }}" style="text-transform:capitalize;">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-secondary">View Order</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="card-body" style="text-align:center; padding:3rem 1.5rem; color:var(--muted-foreground);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px; height:40px; margin:0 auto 1rem; opacity:0.6; display:block;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    <div>No orders found for this customer.</div>
                </div>
            @endif
        </div>
    </div>

    <div>
        <!-- Stats Card -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Lifetime Value</h3>
            </div>
            <div class="card-body" style="display:grid; gap:1rem;">
                <div>
                    <div class="form-hint" style="font-size:0.8125rem; color:var(--muted-foreground); margin-bottom:0.125rem;">Total Spent</div>
                    <div style="font-size:1.5rem; font-weight:700; color:var(--primary);">@money($totalSpent)</div>
                </div>
                <div style="border-top:1px solid var(--border); padding-top:0.75rem;">
                    <div class="form-hint" style="font-size:0.8125rem; color:var(--muted-foreground); margin-bottom:0.125rem;">Total Orders</div>
                    <div style="font-size:1.25rem; font-weight:600;">{{ $totalOrders }}</div>
                </div>
                <div style="border-top:1px solid var(--border); padding-top:0.75rem;">
                    <div class="form-hint" style="font-size:0.8125rem; color:var(--muted-foreground); margin-bottom:0.125rem;">Average Order Value</div>
                    <div style="font-size:1.25rem; font-weight:600;">@money($averageOrderValue)</div>
                </div>
                @if($customer->orders->count())
                    <div style="border-top:1px solid var(--border); padding-top:0.75rem;">
                        <div class="form-hint" style="font-size:0.8125rem; color:var(--muted-foreground); margin-bottom:0.125rem;">First Order Date</div>
                        <div style="font-weight:500;">{{ $customer->orders->last()->created_at?->format('M d, Y') }}</div>
                    </div>
                    <div style="border-top:1px solid var(--border); padding-top:0.75rem;">
                        <div class="form-hint" style="font-size:0.8125rem; color:var(--muted-foreground); margin-bottom:0.125rem;">Last Order Date</div>
                        <div style="font-weight:500;">{{ $customer->orders->first()->created_at?->format('M d, Y') }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Addresses Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Shipping Addresses</h3>
            </div>
            <div class="card-body" style="display:grid; gap:1.25rem;">
                @forelse($shippingAddresses as $index => $address)
                    <div style="{{ $index > 0 ? 'border-top: 1px solid var(--border); padding-top: 1rem;' : '' }}">
                        <div style="font-weight:600; font-size:0.9375rem; margin-bottom:0.25rem;">{{ data_get($address, 'name') }}</div>
                        <div style="font-size:0.875rem; color:var(--muted-foreground); line-height:1.5;">
                            <div>{{ data_get($address, 'phone') }}</div>
                            <div>{{ data_get($address, 'address') }}</div>
                            <div>{{ data_get($address, 'area') }}, {{ data_get($address, 'city') }}</div>
                            <div>Zip: {{ data_get($address, 'zip') }}</div>
                        </div>
                    </div>
                @empty
                    <div style="color:var(--muted-foreground); text-align:center; padding:1rem 0;">
                        No shipping address history found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
