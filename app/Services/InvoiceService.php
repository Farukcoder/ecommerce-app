<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Http\Response;

class InvoiceService
{
    public function downloadInvoice(Order $order): Response
    {
        $data = $this->buildInvoiceData($order);
        $filename = 'invoice-' . $order->order_number . '.pdf';

        if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.invoice', $data);

            return $pdf->download($filename);
        }

        if (class_exists('Spatie\\Browsershot\\Browsershot')) {
            $html = view('orders.invoice', $data)->render();
            $pdfContent = \Spatie\Browsershot\Browsershot::html($html)
                ->format('A4')
                ->pdf();

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return response()->json([
            'message' => 'PDF generator is not installed. Please install dompdf or browswershot.',
        ], 501);
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
