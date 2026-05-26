<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Http\Response;

class InvoiceService
{
    public function downloadInvoice(Order $order): Response
    {
        $data = $this->buildInvoiceData($order);
        $filename = 'invoice-' . $order->order_number . '.pdf';

        $pdfContent = Pdf::loadView('orders.invoice', $data)
            ->setPaper('a4')
            ->output();

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function buildInvoiceData(Order $order): array
    {
        $order->loadMissing(['customer', 'items.product']);
        $settings = SystemSetting::query()->latest()->first();

        return [
            'order' => $order,
            'settings' => $settings,
            'issuedAt' => now(),
            'dueAt' => now()->addDays(7),
        ];
    }
}
