@extends('tyro-dashboard::layouts.user')

@section('title', 'Order Details')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('orders.index') }}">Orders</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $order->order_number }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Order {{ $order->order_number }}</h1>
            <p class="page-description">Placed on {{ $order->created_at?->format('M d, Y h:i A') }}</p>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <a href="{{ route('orders.invoice', $order) }}" class="btn btn-secondary">Print Invoice</a>
            <a href="{{ route('orders.index') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr); gap: 1.5rem; align-items:start;">
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <h3 class="card-title">Order Items</h3>
                <span class="badge badge-secondary">{{ $order->items->count() }} items</span>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div style="font-weight:500;">{{ $item->product_name }}</div>
                                    <div style="font-size:0.8125rem; color:var(--muted-foreground);">{{ $item->product?->name }}</div>
                                </td>
                                <td>{{ $item->product_sku ?? '—' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>৳{{ number_format($item->unit_price, 2) }}</td>
                                <td style="font-weight:600;">৳{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body" style="border-top:1px solid var(--border);">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:0.75rem;">
                    <div>
                        <div class="form-hint">Subtotal</div>
                        <div style="font-weight:600;">৳{{ number_format($order->subtotal, 2) }}</div>
                    </div>
                    <div>
                        <div class="form-hint">Discount</div>
                        <div style="font-weight:600;">৳{{ number_format($order->discount_amount, 2) }}</div>
                    </div>
                    <div>
                        <div class="form-hint">Shipping</div>
                        <div style="font-weight:600;">৳{{ number_format($order->shipping_charge, 2) }}</div>
                    </div>
                    <div>
                        <div class="form-hint">Tax</div>
                        <div style="font-weight:600;">৳{{ number_format($order->tax_amount, 2) }}</div>
                    </div>
                    <div>
                        <div class="form-hint">Total</div>
                        <div style="font-weight:700; font-size:1.125rem;">৳{{ number_format($order->total_amount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($order->note)
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header">
                    <h3 class="card-title">Customer Note</h3>
                </div>
                <div class="card-body" style="line-height:1.6;">
                    {{ $order->note }}
                </div>
            </div>
        @endif

        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <h3 class="card-title">Admin Note</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('orders.note', $order) }}">
                    @csrf
                    @method('PATCH')
                    <textarea name="admin_note" class="form-textarea" rows="4" placeholder="Internal note...">{{ old('admin_note', $order->admin_note) }}</textarea>
                    <div style="display:flex; justify-content:flex-end; margin-top:0.75rem;">
                        <button type="submit" class="btn btn-primary">Save Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <h3 class="card-title">Order Status</h3>
            </div>
            <div class="card-body">
                <div style="margin-bottom:0.75rem; font-size:0.9375rem;">
                    Current: <strong style="text-transform:capitalize;">{{ $order->status }}</strong>
                </div>
                <form method="POST" action="{{ route('orders.status', $order) }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label class="form-label">Next Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">Select status</option>
                            @foreach($nextStatuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Note (optional)</label>
                        <textarea name="note" class="form-textarea" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary" {{ empty($nextStatuses) ? 'disabled' : '' }}>Update Status</button>
                </form>
            </div>
        </div>

        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <h3 class="card-title">Customer</h3>
            </div>
            <div class="card-body">
                <div style="font-weight:600;">{{ $order->customer?->name ?? 'Guest' }}</div>
                <div style="color:var(--muted-foreground);">{{ $order->customer?->email ?? '—' }}</div>
                <div style="margin-top:0.5rem; font-size:0.875rem;">
                    Past orders: {{ $customerOrderCount }}
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <h3 class="card-title">Shipping Address</h3>
            </div>
            <div class="card-body" style="line-height:1.6;">
                <div>{{ data_get($order->shipping_address, 'name') }}</div>
                <div>{{ data_get($order->shipping_address, 'phone') }}</div>
                <div>{{ data_get($order->shipping_address, 'address') }}</div>
                <div>{{ data_get($order->shipping_address, 'area') }}, {{ data_get($order->shipping_address, 'city') }}</div>
                <div>{{ data_get($order->shipping_address, 'zip') }}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <h3 class="card-title">Payment Info</h3>
            </div>
            <div class="card-body" style="line-height:1.6;">
                <div>Method: <strong>{{ strtoupper($order->payment_method ?? 'N/A') }}</strong></div>
                <div>Status: <strong style="text-transform:capitalize;">{{ $order->payment_status }}</strong></div>
                <div>Transaction: <span style="color:var(--muted-foreground);">{{ data_get($order->shipping_address, 'transaction_id', '—') }}</span></div>
            </div>
        </div>

        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <h3 class="card-title">Status Timeline</h3>
            </div>
            <div class="card-body">
                @forelse($order->statusLogs as $log)
                    <div style="border-left:2px solid var(--border); padding-left:0.75rem; margin-bottom:0.75rem;">
                        <div style="font-weight:600; text-transform:capitalize;">{{ $log->from_status }} → {{ $log->to_status }}</div>
                        <div style="font-size:0.8125rem; color:var(--muted-foreground);">{{ $log->created_at?->format('M d, Y h:i A') }} · {{ $log->changedBy?->name ?? 'System' }}</div>
                        @if($log->note)
                            <div style="font-size:0.875rem; margin-top:0.25rem;">{{ $log->note }}</div>
                        @endif
                    </div>
                @empty
                    <div style="color:var(--muted-foreground);">No status updates yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card" style="border-color: color-mix(in srgb, var(--destructive), transparent 70%);">
            <div class="card-header">
                <h3 class="card-title">Danger Zone</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('orders.cancel', $order) }}" style="margin-bottom:1rem;">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Cancel reason</label>
                        <textarea name="reason" class="form-textarea" rows="2" {{ in_array($order->status, ['pending','processing','shipped']) ? '' : 'disabled' }}></textarea>
                    </div>
                    <button type="submit" class="btn" style="background: color-mix(in srgb, var(--destructive), transparent 85%); color: var(--destructive);" {{ in_array($order->status, ['pending','processing','shipped']) ? '' : 'disabled' }}>
                        Cancel Order
                    </button>
                </form>

                <form method="POST" action="{{ route('orders.refund', $order) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Refund amount (max {{ number_format($remainingRefund, 2) }})</label>
                        <input type="number" step="0.01" max="{{ $remainingRefund }}" name="amount" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Method</label>
                        <select name="method" class="form-select" required>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-textarea" rows="2" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary">Request Refund</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
