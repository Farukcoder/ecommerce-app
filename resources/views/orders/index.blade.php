@extends('tyro-dashboard::layouts.user')

@section('title', 'Orders')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Orders</span>
@endsection

@section('content')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Orders</h1>
            <p class="page-description">Track, update and manage customer orders.</p>
        </div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Orders (Today)</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['total_orders'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Revenue (Today)</div>
            <div class="stat-value" style="font-size:1.5rem;">@money($summary['total_revenue'])</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Pending (Today)</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['pending_count'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Cancelled (Today)</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['cancelled_count'] }}</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <form action="{{ route('orders.index') }}" method="GET">
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" class="form-input" placeholder="Search order # or customer" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status:</label>
                    <select name="status" class="form-select" style="min-width: 140px;">
                        <option value="">All</option>
                        @foreach(['pending','processing','shipped','delivered','cancelled','refunded'] as $status)
                            <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Payment:</label>
                    <select name="payment_status" class="form-select" style="min-width: 140px;">
                        <option value="">All</option>
                        @foreach(['unpaid','paid','partial','refunded'] as $payStatus)
                            <option value="{{ $payStatus }}" {{ ($filters['payment_status'] ?? '') === $payStatus ? 'selected' : '' }}>{{ ucfirst($payStatus) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Method:</label>
                    <select name="payment_method" class="form-select" style="min-width: 140px;">
                        <option value="">All</option>
                        @foreach(['bkash','nagad','card','cod'] as $method)
                            <option value="{{ $method }}" {{ ($filters['payment_method'] ?? '') === $method ? 'selected' : '' }}>{{ strtoupper($method) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">From:</label>
                    <input type="text" name="date_from" class="form-input" value="{{ $filters['date_from'] ?? '' }}" data-calendar-field data-placeholder="Pick a start date" autocomplete="off">
                </div>
                <div class="filter-group">
                    <label class="filter-label">To:</label>
                    <input type="text" name="date_to" class="form-input" value="{{ $filters['date_to'] ?? '' }}" data-calendar-field data-placeholder="Pick an end date" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(array_filter($filters))
                    <a href="{{ route('orders.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('orders.bulk-status') }}">
        @csrf
        <div class="card-header" style="padding: 0.875rem 1.5rem;">
            <div style="display:flex; align-items:center; gap:0.75rem; width:100%;">
                <input type="checkbox" id="select-all" class="checkbox-input" style="cursor:pointer;">
                <label for="select-all" style="font-size:0.875rem; color:var(--muted-foreground); cursor:pointer;">
                    Select all on this page
                </label>
                <span id="selected-count" style="font-size:0.875rem; color:var(--muted-foreground); margin-left:0.5rem;"></span>
                <div style="margin-left:auto; display:flex; gap:0.5rem; align-items:center;">
                    <select name="status" class="form-select" style="min-width:160px;">
                        <option value="">Bulk status update</option>
                        @foreach(['processing','shipped','delivered','cancelled'] as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-secondary">Apply</button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:40px; padding-right:0;">
                            <input type="checkbox" id="select-all-th" class="checkbox-input" style="cursor:pointer;">
                        </th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td style="padding-right:0; width:40px;">
                                <input type="checkbox" class="checkbox-input order-checkbox" name="order_ids[]" value="{{ $order->id }}" style="cursor:pointer;">
                            </td>
                            <td>{{ $order->order_number }}</td>
                            <td>
                                @if($order->customer)
                                    <div style="font-weight:500;">
                                        <a href="{{ route('customers.show', $order->customer) }}" style="color:var(--primary); text-decoration:none; font-weight:600;">{{ $order->customer->name }}</a>
                                    </div>
                                @else
                                    <div style="font-weight:500; color:var(--muted-foreground);">Guest</div>
                                @endif
                                <div style="font-size:0.8125rem; color:var(--muted-foreground);">{{ data_get($order->shipping_address, 'phone') }}</div>
                            </td>
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
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-secondary">View</a>
                                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn btn-sm btn-ghost">Invoice</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; color:var(--muted-foreground); padding:2rem;">
                                No orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <div class="card-footer" style="padding: 1rem 1.5rem;">
        {{ $orders->links() }}
    </div>
</div>

@push('scripts')
<script>
    const selectAll = document.getElementById('select-all');
    const selectAllTh = document.getElementById('select-all-th');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const selectedCount = document.getElementById('selected-count');

    const syncSelectAll = (checked) => {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = checked;
        });
        updateSelectedCount();
    };

    const updateSelectedCount = () => {
        const count = Array.from(checkboxes).filter((checkbox) => checkbox.checked).length;
        selectedCount.textContent = count ? `${count} selected` : '';
    };

    if (selectAll) {
        selectAll.addEventListener('change', (event) => {
            selectAllTh.checked = event.target.checked;
            syncSelectAll(event.target.checked);
        });
    }

    if (selectAllTh) {
        selectAllTh.addEventListener('change', (event) => {
            selectAll.checked = event.target.checked;
            syncSelectAll(event.target.checked);
        });
    }

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fields = document.querySelectorAll('[data-calendar-field]');

        if (!fields.length || typeof flatpickr === 'undefined') {
            return;
        }

        flatpickr(fields, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'M j, Y',
            allowInput: true,
            disableMobile: true,
            onReady: function (_, __, instance) {
                if (instance.altInput) {
                    instance.altInput.setAttribute('placeholder', instance.input.dataset.placeholder || 'Select a date');
                }
            }
        });
    });
</script>
@endpush
@endsection
