<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        @page { margin: 12mm; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; color: #111; margin: 0; padding: 0; }
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
        .print-actions { display: none; }
    </style>
</head>
<body>
<div class="invoice">
    <table style="width:100%; margin-bottom:16px; border-bottom:1px solid #e5e7eb; padding-bottom:8px;">
        <tr>
            <td style="vertical-align:middle;">
                @if(!empty($settings?->system_logo_white_pdf_source))
                    <img src="{{ $settings->system_logo_white_pdf_source }}" alt="{{ __('messages.logo_alt') }}" style="height:48px; display:block; margin-bottom:6px;">
                @endif
            </td>
            <td style="vertical-align:middle; text-align:right; width:260px; font-size:12px; color:#374151;">
                <div style="font-size:16px; font-weight:700; margin-bottom:6px;">{{ __('messages.invoice') }}</div>
                <div>{{ __('messages.invoice_number') }} {{ $order->order_number }}</div>
                <div>{{ __('messages.invoice_date') }} {{ $issuedAt->format('M d, Y') }}</div>
                <div>{{ __('messages.due_date') }} {{ $dueAt->format('M d, Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="card">
        <strong>{{ __('messages.bill_to') }}</strong>
        <div>{{ $order->customer?->name ?? data_get($order->shipping_address, 'name') }}</div>
        <div>{{ data_get($order->shipping_address, 'address') }}</div>
        <div>{{ data_get($order->shipping_address, 'area') }}, {{ data_get($order->shipping_address, 'city') }} {{ data_get($order->shipping_address, 'zip') }}</div>
        <div>{{ data_get($order->shipping_address, 'phone') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('messages.product') }}</th>
                <th>{{ __('messages.sku') }}</th>
                <th>{{ __('messages.qty') }}</th>
                <th>{{ __('messages.unit_price') }}</th>
                <th>{{ __('messages.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->product_sku ?? '—' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>@money($item->unit_price)</td>
                    <td>@money($item->total_price)</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="card" style="margin-top: 16px;">
        <table class="totals">
            <tr>
                <td>{{ __('messages.subtotal') }}</td>
                <td style="text-align:right;">@money($order->subtotal)</td>
            </tr>
            <tr>
                <td>{{ __('messages.discount') }}</td>
                <td style="text-align:right;">@money($order->discount_amount)</td>
            </tr>
            <tr>
                <td>{{ __('messages.shipping') }}</td>
                <td style="text-align:right;">@money($order->shipping_charge)</td>
            </tr>
            <tr>
                <td>{{ __('messages.tax') }}</td>
                <td style="text-align:right;">@money($order->tax_amount)</td>
            </tr>
            <tr>
                <td>{{ __('messages.grand_total') }}</td>
                <td style="text-align:right;">@money($order->total_amount)</td>
            </tr>
        </table>
        <div style="margin-top: 12px; font-size: 13px;">
            {{ __('messages.payment_method_label') }} {{ strtoupper($order->payment_method ?? 'N/A') }}<br>
            {{ __('messages.payment_status_label') }} {{ ucfirst($order->payment_status) }}
        </div>
    </div>

    <div class="footer">
        {{ __('messages.invoice_thank_you') }}
    </div>

    <div class="print-actions" style="margin-top: 16px;">
        <button onclick="window.print()" style="padding: 8px 14px; border-radius: 8px; border: 1px solid #e5e7eb; background: #111; color: #fff; cursor: pointer;">{{ __('messages.print_button') }}</button>
    </div>
</div>
</body>
</html>
