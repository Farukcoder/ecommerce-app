<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <style>
        @page {
            margin: 10mm;
        }
        body {
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            color: #111827;
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-size: 12px;
        }
        .page {
            padding: 0;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
        }
        .header-brand,
        .header-meta {
            display: table-cell;
            vertical-align: middle;
        }
        .header-brand {
            width: 70%;
        }
        .header-meta {
            width: 30%;
            text-align: right;
            font-size: 11px;
            color: #6b7280;
        }
        .logo {
            max-height: 52px;
            max-width: 160px;
            margin-bottom: 6px;
        }
        .header {
            margin-bottom: 16px;
        }
        .title {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }
        .subtitle {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        .meta {
            margin-top: 8px;
            font-size: 12px;
            color: #374151;
        }
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin: 16px 0 20px;
        }
        .stats-table td {
            width: 20%;
            vertical-align: top;
        }
        .stat-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            min-height: 54px;
        }
        .stat-label {
            font-size: 11px;
            color: #6b7280;
        }
        .stat-value {
            font-size: 14px;
            font-weight: 700;
            margin-top: 6px;
        }
        .section {
            margin-top: 14px;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 8px;
            color: #111827;
        }
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .report-table th, .report-table td {
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 6px;
        }
        .report-table th {
            background: #f9fafb;
            font-weight: 600;
        }
        .muted {
            color: #6b7280;
        }
        .footer {
            margin-top: 16px;
            font-size: 11px;
            color: #9ca3af;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="page">
    <div class="header">
        <div class="header-brand">
            @if(!empty($brand['logo']))
                <img class="logo" src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }} logo">
            @endif
        </div>
        <div class="header-meta">
            <div><strong>Sales Report</strong></div>
            <div>Range: {{ ucfirst($filters['range'] ?? 'monthly') }}</div>
            <div>Period: {{ $filters['date_from'] }} to {{ $filters['date_to'] }}</div>
            @if(!empty($filters['status']))
                <div>Status: {{ ucfirst($filters['status']) }}</div>
            @endif
            <div>Generated: {{ now()->format('M d, Y H:i') }}</div>
        </div>
    </div>


    <div class="section">
        <div class="section-title">Order Wise Sales</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>{{ $order['order_number'] }}</td>
                    <td>{{ $order['customer_name'] }}</td>
                    <td>{{ ucfirst($order['status']) }}</td>
                    <td>{{ ucfirst($order['payment_status']) }}</td>
                    <td>{{ $order['items_count'] }}</td>
                    <td>BDT {{ number_format($order['total_amount'], 2) }}</td>
                    <td>{{ optional($order['created_at'])->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="muted">No orders found for the selected range.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Product Wise Sales</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Orders</th>
                    <th>Quantity</th>
                    <th>Avg. Unit Price</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->product_sku ?? '-' }}</td>
                    <td>{{ $product->order_count }}</td>
                    <td>{{ $product->total_quantity }}</td>
                    <td>BDT {{ number_format((float) $product->avg_unit_price, 2) }}</td>
                    <td>BDT {{ number_format((float) $product->total_revenue, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="muted">No product sales found for the selected range.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Daily Breakdown</div>
        <table class="report-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Orders</th>
                <th>Total Revenue</th>
                <th>Average Order</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daily as $row)
            <tr>
                <td>{{ \Illuminate\Support\Carbon::parse($row->order_date)->format('M d, Y') }}</td>
                <td>{{ $row->order_count }}</td>
                <td>BDT {{ number_format((float) $row->total_revenue, 2) }}</td>
                <td>BDT {{ number_format((float) $row->avg_order_value, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="muted">No sales found for the selected range.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top: 20px; border-top: 2px solid #111827; padding: 10px 0; font-size: 13px; font-weight: bold; overflow: hidden;">
        <div style="float: left; width: 50%;">
            Total Orders: {{ $summary['total_orders'] }}
        </div>
        <div style="float: right; width: 50%; text-align: right;">
            Total Revenue: BDT {{ number_format($summary['total_revenue'], 2) }}
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        Generated at {{ now()->format('M d, Y H:i') }}
    </div>
    </div>
</body>
</html>
