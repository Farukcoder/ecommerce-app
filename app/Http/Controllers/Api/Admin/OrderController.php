<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoiceService;
use App\Services\OrderWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
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

        $sortBy = $request->string('sort_by', 'created_at')->toString();
        $sortOrder = strtolower($request->string('sort_order', 'desc')->toString()) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'total_amount', 'status', 'order_number'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortOrder);

        $limit = max(1, min((int) $request->integer('limit', 20), 100));
        $orders = $query->paginate($limit)->withQueryString();

        $summaryBase = clone $query;
        $summary = [
            'total_orders' => (clone $summaryBase)->count(),
            'total_revenue' => (float) (clone $summaryBase)->sum('total_amount'),
            'pending_count' => (clone $summaryBase)->where('status', 'pending')->count(),
            'cancelled_count' => (clone $summaryBase)->where('status', 'cancelled')->count(),
        ];

        $data = $orders->getCollection()->map(function (Order $order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => [
                    'id' => $order->customer?->id,
                    'name' => $order->customer?->name,
                ],
                'items_count' => $order->items_count,
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'created_at' => $order->created_at,
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'total' => $orders->total(),
                'page' => $orders->currentPage(),
                'limit' => $orders->perPage(),
                'total_pages' => $orders->lastPage(),
            ],
            'summary' => $summary,
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load([
            'customer',
            'items.product',
            'statusLogs.changedBy',
            'refunds',
        ]);

        return response()->json([
            'data' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order, OrderWorkflowService $workflow): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $updated = $workflow->changeStatus($order, $data['status'], $request->user()?->id, $data['note'] ?? null);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => $updated->load('statusLogs'),
        ]);
    }

    public function cancel(Request $request, Order $order, OrderWorkflowService $workflow): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $updated = $workflow->cancelOrder($order, $data['reason'], $request->user()?->id);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => $updated->load('statusLogs'),
        ]);
    }

    public function refund(Request $request, Order $order, OrderWorkflowService $workflow): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string'],
            'method' => ['required', 'string', 'max:50'],
        ]);

        $refund = $workflow->createRefund(
            $order,
            (float) $data['amount'],
            $data['reason'],
            $data['method'],
            $request->user()?->id
        );

        return response()->json([
            'message' => 'Refund request created.',
            'data' => $refund,
        ], 201);
    }

    public function updateAdminNote(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $order->update(['admin_note' => $data['admin_note'] ?? null]);

        return response()->json([
            'message' => 'Admin note updated.',
            'data' => $order,
        ]);
    }

    public function invoice(Order $order, InvoiceService $invoiceService)
    {
        return $invoiceService->downloadInvoice($order);
    }
}
