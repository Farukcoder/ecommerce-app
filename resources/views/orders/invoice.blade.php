<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; color: #111; margin: 0; padding: 24px; }
        .invoice { max-width: 900px; margin: 0 auto; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
        .brand { display: flex; gap: 16px; align-items: center; }
        .brand img { height: 48px; }
        .brand h1 { font-size: 20px; margin: 0; }
        .meta { text-align: right; }
        .meta h2 { margin: 0 0 8px; }
        .card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #e5e7eb; }
        th { background: #f8fafc; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        .totals { margin-left: auto; width: 320px; }
        .totals td { border: none; padding: 4px 0; }
        .totals tr:last-child td { font-weight: 700; font-size: 16px; }
        .footer { margin-top: 24px; font-size: 12px; color: #6b7280; }
        @media print { .print-actions { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
<div class="invoice">
    <div class="top">
        <div class="brand">
            @if($settings?->system_logo_black)
                <img src="{{ asset('storage/' . $settings->system_logo_black) }}" alt="Logo">
            @endif
            <div>
                <h1>{{ $settings?->system_name ?? config('app.name') }}</h1>
                <div style="font-size:12px; color:#6b7280;">{{ $settings?->frontend_website_name ?? 'Online Store' }}</div>
            </div>
        </div>
        <div class="meta">
            <h2>Invoice</h2>
            <div>Invoice #: {{ $order->order_number }}</div>
            <div>Invoice Date: {{ $issuedAt->format('M d, Y') }}</div>
            <div>Due Date: {{ $dueAt->format('M d, Y') }}</div>
        </div>
    </div>

    <div class="card">
        <strong>Bill To</strong>
        <div>{{ $order->customer?->name ?? data_get($order->shipping_address, 'name') }}</div>
        <div>{{ data_get($order->shipping_address, 'address') }}</div>
        <div>{{ data_get($order->shipping_address, 'area') }}, {{ data_get($order->shipping_address, 'city') }} {{ data_get($order->shipping_address, 'zip') }}</div>
        <div>{{ data_get($order->shipping_address, 'phone') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->product_sku ?? '—' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>৳{{ number_format($item->unit_price, 2) }}</td>
                    <td>৳{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="card" style="margin-top: 16px;">
        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td style="text-align:right;">৳{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td style="text-align:right;">৳{{ number_format($order->discount_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Shipping</td>
                <td style="text-align:right;">৳{{ number_format($order->shipping_charge, 2) }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td style="text-align:right;">৳{{ number_format($order->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Grand Total</td>
                <td style="text-align:right;">৳{{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </table>
        <div style="margin-top: 12px; font-size: 13px;">
            Payment Method: {{ strtoupper($order->payment_method ?? 'N/A') }}<br>
            Payment Status: {{ ucfirst($order->payment_status) }}
        </div>
    </div>

    <div class="footer">
        Thank you for your purchase. Please keep this invoice for your records. Returns are accepted within 7 days with original packaging.
    </div>

    <div class="print-actions" style="margin-top: 16px;">
        <button onclick="window.print()" style="padding: 8px 14px; border-radius: 8px; border: 1px solid #e5e7eb; background: #111; color: #fff; cursor: pointer;">Print</button>
    </div>
</div>
</body>
</html>
