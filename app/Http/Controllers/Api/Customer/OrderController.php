<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderPlaced;
use App\Services\CustomerCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private readonly CustomerCheckoutService $checkoutService) {}

    public function options(): JsonResponse
    {
        return response()->json([
            'data' => $this->checkoutService->options(),
        ]);
    }

    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json([
            'data' => $this->checkoutService->quote($data['items']),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $customer = $this->authorizeCustomer($request);
        $orders = $this->checkoutService->ordersQueryFor($customer)
            ->paginate((int) $request->integer('per_page', 15));

        $data = $orders->getCollection()->map(fn (Order $order) => $this->formatOrder($order, false));

        return response()->json([
            'data' => $data,
            'pagination' => [
                'total' => $orders->total(),
                'page' => $orders->currentPage(),
                'limit' => $orders->perPage(),
                'total_pages' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $customer = $this->authorizeCustomer($request);
        $order = $this->checkoutService->resolveOrderFor($customer, $order);

        return response()->json([
            'data' => $this->formatOrder($order),
        ]);
    }

    public function track(Request $request, string $orderIdentifier): JsonResponse
    {
        $customer = $this->authorizeCustomer($request);
        $order = $this->resolveOrderByIdentifier($customer, $orderIdentifier);

        return response()->json([
            'data' => $this->formatTrackingOrder($order),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $customer = $this->authorizeCustomer($request);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'apartment' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:50'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = $this->checkoutService->placeOrder($customer, $data);

        $customer->notify(new OrderPlaced($order));

        return response()->json([
            'message' => 'Order placed successfully.',
            'data' => $this->formatOrder($order),
        ], 201);
    }

    private function formatOrder(Order $order, bool $includeRelations = true): array
    {
        $payload = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'subtotal' => (float) $order->subtotal,
            'discount_amount' => (float) $order->discount_amount,
            'shipping_charge' => (float) $order->shipping_charge,
            'tax_amount' => (float) $order->tax_amount,
            'total_amount' => (float) $order->total_amount,
            'created_at' => $order->created_at,
        ];

        if (! $includeRelations) {
            $payload['items_count'] = $order->items->count();

            return $payload;
        }

        $payload['shipping_address'] = $order->shipping_address;
        $payload['note'] = $order->note;
        $payload['items'] = $order->items->map(fn ($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'product_sku' => $item->product_sku,
            'quantity' => (int) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'total_price' => (float) $item->total_price,
            'product' => $item->product ? [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
            ] : null,
        ])->values();
        $payload['status_logs'] = $order->statusLogs->map(fn ($log) => [
            'id' => $log->id,
            'from_status' => $log->from_status,
            'to_status' => $log->to_status,
            'note' => $log->note,
            'created_at' => $log->created_at,
            'changed_by' => $log->changedBy ? [
                'id' => $log->changedBy->id,
                'name' => $log->changedBy->name,
            ] : null,
        ])->values();

        return $payload;
    }

    private function formatTrackingOrder(Order $order): array
    {
        $payload = $this->formatOrder($order);
        $payload['estimated_delivery'] = $this->estimateDeliveryDate($order);
        $payload['tracking_events'] = $this->buildTrackingEvents($order);

        return $payload;
    }

    private function resolveOrderByIdentifier(User $customer, string $orderIdentifier): Order
    {
        $query = Order::query()
            ->with(['customer', 'items.product', 'statusLogs.changedBy', 'refunds'])
            ->where('customer_id', $customer->id);

        if (ctype_digit($orderIdentifier)) {
            $query->where('id', (int) $orderIdentifier);
        } else {
            $query->where('order_number', $orderIdentifier);
        }

        return $query->firstOrFail();
    }

    private function buildTrackingEvents(Order $order): array
    {
        $eventDefinitions = [
            'pending' => [
                'status' => 'Order Placed',
                'description' => 'Your order has been confirmed.',
            ],
            'processing' => [
                'status' => 'Processing',
                'description' => 'Your order is being prepared.',
            ],
            'shipped' => [
                'status' => 'Shipped',
                'description' => 'Your order is on its way.',
            ],
            'delivered' => [
                'status' => 'Delivered',
                'description' => 'Your order has been delivered.',
            ],
            'cancelled' => [
                'status' => 'Cancelled',
                'description' => 'This order has been cancelled.',
            ],
            'refunded' => [
                'status' => 'Refunded',
                'description' => 'A refund has been completed for this order.',
            ],
        ];

        $timeline = [];
        $timelineDates = ['pending' => $order->created_at?->toIso8601String()];

        foreach ($order->statusLogs as $log) {
            $timelineDates[$log->to_status] = $log->created_at?->toIso8601String();
        }

        foreach (['pending', 'processing', 'shipped', 'delivered'] as $status) {
            $timeline[] = [
                'status' => $eventDefinitions[$status]['status'],
                'description' => $eventDefinitions[$status]['description'],
                'date' => $timelineDates[$status] ?? null,
                'completed' => $this->isStatusReached($order->status, $status),
            ];
        }

        if (in_array($order->status, ['cancelled', 'refunded'], true)) {
            $timeline[] = [
                'status' => $eventDefinitions[$order->status]['status'],
                'description' => $eventDefinitions[$order->status]['description'],
                'date' => $timelineDates[$order->status] ?? $order->updated_at?->toIso8601String(),
                'completed' => true,
            ];
        }

        return $timeline;
    }

    private function isStatusReached(string $currentStatus, string $status): bool
    {
        $order = ['pending', 'processing', 'shipped', 'delivered'];

        if ($currentStatus === 'cancelled') {
            return in_array($status, ['pending', 'processing', 'shipped'], true);
        }

        if ($currentStatus === 'refunded') {
            return true;
        }

        $currentIndex = array_search($currentStatus, $order, true);
        $statusIndex = array_search($status, $order, true);

        if ($currentIndex === false || $statusIndex === false) {
            return false;
        }

        return $statusIndex <= $currentIndex;
    }

    private function estimateDeliveryDate(Order $order): ?string
    {
        return match ($order->status) {
            'pending', 'processing' => $order->created_at?->copy()?->addDays(3)?->toDateString(),
            'shipped' => $order->updated_at?->copy()?->addDays(2)?->toDateString(),
            'delivered', 'cancelled', 'refunded' => null,
            default => null,
        };
    }

    private function authorizeCustomer(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            throw ValidationException::withMessages([
                'user' => 'Unauthorized.',
            ]);
        }

        if (method_exists($user, 'hasRole') && ! $user->hasRole('customer')) {
            abort(403, 'This account is not allowed to use the customer API.');
        }

        if (method_exists($user, 'roles') && ! method_exists($user, 'hasRole') && ! $user->roles()->where('slug', 'customer')->exists()) {
            abort(403, 'This account is not allowed to use the customer API.');
        }

        return $user;
    }
}
