<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\ProductStock;
use App\Models\Refund;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderWorkflowService
{
    public const STATUS_TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
        'refunded' => [],
    ];

    public function getNextStatuses(string $status): array
    {
        return self::STATUS_TRANSITIONS[$status] ?? [];
    }

    public function assertTransitionAllowed(string $fromStatus, string $toStatus): void
    {
        if (! in_array($toStatus, $this->getNextStatuses($fromStatus), true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid status transition.',
            ]);
        }
    }

    public function changeStatus(Order $order, string $toStatus, ?int $userId, ?string $note = null): Order
    {
        $fromStatus = $order->status;
        $this->assertTransitionAllowed($fromStatus, $toStatus);

        return DB::transaction(function () use ($order, $fromStatus, $toStatus, $userId, $note) {
            $order->update(['status' => $toStatus]);

            $this->logStatusChange($order, $fromStatus, $toStatus, $userId, $note);

            if ($toStatus === 'cancelled') {
                $this->restoreStockForOrder($order, $userId, 'Stock returned after cancellation.');
            }

            return $order->refresh();
        });
    }

    public function cancelOrder(Order $order, string $reason, ?int $userId): Order
    {
        if (! in_array($order->status, ['pending', 'processing', 'shipped'], true)) {
            throw ValidationException::withMessages([
                'status' => 'This order cannot be cancelled in its current state.',
            ]);
        }

        return DB::transaction(function () use ($order, $reason, $userId) {
            $fromStatus = $order->status;

            $order->update([
                'status' => 'cancelled',
                'cancelled_reason' => $reason,
            ]);

            $this->logStatusChange($order, $fromStatus, 'cancelled', $userId, $reason);
            $this->restoreStockForOrder($order, $userId, 'Stock returned after cancellation.');

            return $order->refresh();
        });
    }

    public function createRefund(Order $order, float $amount, string $reason, string $method, ?int $userId): Refund
    {
        $remaining = $this->getRemainingRefundableAmount($order);
        if ($amount > $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'Refund amount exceeds the remaining refundable total.',
            ]);
        }

        return $order->refunds()->create([
            'amount' => $amount,
            'reason' => $reason,
            'method' => $method,
            'status' => 'pending',
            'processed_by' => $userId,
        ]);
    }

    public function updateRefundStatus(Refund $refund, string $status, ?string $note, ?int $userId): Refund
    {
        $refund->update([
            'status' => $status,
            'note' => $note,
            'processed_by' => $userId,
        ]);

        if (in_array($status, ['approved', 'completed'], true)) {
            $refund->order()->update(['payment_status' => 'refunded']);
        }

        if ($status === 'completed') {
            $order = $refund->order;
            if ($order && $order->status === 'delivered') {
                $order->update(['status' => 'refunded']);
                $this->logStatusChange($order, 'delivered', 'refunded', $userId, 'Order refunded.');
            }
        }

        return $refund->refresh();
    }

    public function getRemainingRefundableAmount(Order $order): float
    {
        $refunded = $order->refunds()
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->sum('amount');

        return max(0, (float) $order->total_amount - (float) $refunded);
    }

    protected function restoreStockForOrder(Order $order, ?int $userId, ?string $note): void
    {
        $order->loadMissing('items', 'items.product');

        foreach ($order->items as $item) {
            $stock = ProductStock::query()
                ->where('product_id', $item->product_id)
                ->orderBy('id')
                ->first();

            if (! $stock) {
                $stock = ProductStock::create([
                    'product_id' => $item->product_id,
                    'sku' => $item->product_sku ?: 'STOCK-' . $item->product_id . '-' . Str::upper(Str::random(6)),
                    'quantity' => 0,
                ]);
            }

            $stock->increaseStock($item->quantity);

            StockLog::create([
                'product_id' => $item->product_id,
                'order_id' => $order->id,
                'quantity' => $item->quantity,
                'change_type' => 'return',
                'note' => $note,
                'created_by' => $userId,
                'created_at' => now(),
            ]);
        }
    }

    protected function logStatusChange(Order $order, string $fromStatus, string $toStatus, ?int $userId, ?string $note): void
    {
        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $userId,
            'note' => $note,
            'created_at' => now(),
        ]);
    }
}
