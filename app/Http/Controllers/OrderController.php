<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()
            ->with('customer')
            ->withCount('items');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($paymentStatus = $request->string('payment_status')->toString()) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($paymentMethod = $request->string('payment_method')->toString()) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($dateFrom = $request->string('date_from')->toString()) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->string('date_to')->toString()) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('shipping_address->phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $today = now()->toDateString();
        $summaryQuery = Order::query()->whereDate('created_at', $today);
        $summary = [
            'total_orders' => $summaryQuery->count(),
            'total_revenue' => (float) $summaryQuery->sum('total_amount'),
            'pending_count' => (clone $summaryQuery)->where('status', 'pending')->count(),
            'cancelled_count' => (clone $summaryQuery)->where('status', 'cancelled')->count(),
        ];

        $filters = $request->only(['search', 'status', 'payment_status', 'payment_method', 'date_from', 'date_to']);

        return view('orders.index', compact('orders', 'summary', 'filters'));
    }

    public function show(Order $order, OrderWorkflowService $workflow)
    {
        $order->load([
            'customer',
            'items.product',
            'statusLogs.changedBy',
            'refunds',
        ]);

        $nextStatuses = $workflow->getNextStatuses($order->status);
        $remainingRefund = $workflow->getRemainingRefundableAmount($order);
        $customerOrderCount = $order->customer
            ? Order::query()->where('customer_id', $order->customer_id)->count()
            : 0;

        return view('orders.show', compact('order', 'nextStatuses', 'remainingRefund', 'customerOrderCount'));
    }

    public function updateStatus(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $workflow->changeStatus($order, $data['status'], $request->user()?->id, $data['note'] ?? null);

        return back()->with('success', 'Order status updated successfully.');
    }

    public function cancel(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $workflow->cancelOrder($order, $data['reason'], $request->user()?->id);

        return back()->with('success', 'Order cancelled successfully.');
    }

    public function refund(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string'],
            'method' => ['required', 'string', 'max:50'],
        ]);

        $workflow->createRefund(
            $order,
            (float) $data['amount'],
            $data['reason'],
            $data['method'],
            $request->user()?->id
        );

        return back()->with('success', 'Refund request created.');
    }

    public function updateAdminNote(Request $request, Order $order)
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $order->update(['admin_note' => $data['admin_note'] ?? null]);

        return back()->with('success', 'Admin note updated.');
    }

    public function bulkUpdateStatus(Request $request, OrderWorkflowService $workflow)
    {
        $data = $request->validate([
            'order_ids' => ['required', 'array'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
            'status' => ['required', Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])],
        ]);

        $orders = Order::query()->whereIn('id', $data['order_ids'])->get();

        foreach ($orders as $order) {
            $workflow->changeStatus($order, $data['status'], $request->user()?->id, 'Bulk status update.');
        }

        return back()->with('success', 'Selected orders updated successfully.');
    }

    public function invoice(Order $order, InvoiceService $invoiceService)
    {
        return view('orders.invoice', $invoiceService->buildInvoiceData($order));
    }
}
