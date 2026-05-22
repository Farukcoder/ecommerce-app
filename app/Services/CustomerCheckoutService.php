<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerCheckoutService
{
    private const TAX_RATE = 0.08;

    private const SHIPPING_CHARGE = 0.00;

    private const PAYMENT_METHODS = [
        [
            'value' => 'bkash',
            'label' => 'bKash',
            'description' => 'Pay with your bKash mobile wallet',
        ],
        [
            'value' => 'nagad',
            'label' => 'Nagad',
            'description' => 'Pay with your Nagad mobile wallet',
        ],
        [
            'value' => 'card',
            'label' => 'Credit / Debit Card',
            'description' => 'Visa, Mastercard, American Express',
        ],
        [
            'value' => 'cod',
            'label' => 'Cash on Delivery',
            'description' => 'Pay when you receive your order',
        ],
    ];

    public function options(): array
    {
        return [
            'tax_rate' => self::TAX_RATE,
            'shipping_charge' => self::SHIPPING_CHARGE,
            'payment_methods' => self::PAYMENT_METHODS,
        ];
    }

    public function quote(array $items): array
    {
        $normalizedItems = $this->normalizeItems($items);
        $products = $this->loadProducts(array_keys($normalizedItems));
        $lineItems = [];
        $subtotal = 0.0;

        foreach ($normalizedItems as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => ["Product {$productId} was not found."],
                ]);
            }

            $availableStock = (int) $product->stocks->sum('quantity');
            if ($availableStock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => ["Insufficient stock for {$product->name}."],
                ]);
            }

            $unitPrice = $this->resolveUnitPrice($product);
            $lineTotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineTotal;

            $lineItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku ?: $product->stocks->first()?->sku,
                'quantity' => $quantity,
                'available_stock' => $availableStock,
                'unit_price' => $unitPrice,
                'total_price' => $lineTotal,
                'image' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
            ];
        }

        return $this->buildTotalsPayload($lineItems, $subtotal);
    }

    public function placeOrder(User $customer, array $payload): Order
    {
        return DB::transaction(function () use ($customer, $payload) {
            $quote = $this->quoteForTransaction($payload['items'] ?? []);
            $shippingAddress = $this->normalizeShippingAddress($payload, $customer);
            $paymentMethod = $this->normalizePaymentMethod($payload['payment_method'] ?? null);
            $note = trim((string) ($payload['note'] ?? '')) ?: null;

            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $paymentMethod,
                'subtotal' => $quote['subtotal'],
                'discount_amount' => 0,
                'shipping_charge' => $quote['shipping_charge'],
                'tax_amount' => $quote['tax_amount'],
                'total_amount' => $quote['total_amount'],
                'shipping_address' => $shippingAddress,
                'note' => $note,
            ]);

            foreach ($quote['items'] as $lineItem) {
                $order->items()->create([
                    'product_id' => $lineItem['product_id'],
                    'product_name' => $lineItem['product_name'],
                    'product_sku' => $lineItem['product_sku'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['unit_price'],
                    'total_price' => $lineItem['total_price'],
                ]);

                $this->reserveStock($lineItem['product_id'], $lineItem['quantity'], $order->id, $customer->id);
            }

            return $order->load(['customer', 'items.product']);
        });
    }

    public function ordersQueryFor(User $customer)
    {
        return Order::query()
            ->with(['items.product'])
            ->where('customer_id', $customer->id)
            ->latest();
    }

    public function resolveOrderFor(User $customer, Order $order): Order
    {
        if ($order->customer_id !== $customer->id) {
            throw ValidationException::withMessages([
                'order' => 'You are not allowed to access this order.',
            ]);
        }

        return $order->load(['customer', 'items.product', 'statusLogs.changedBy', 'refunds']);
    }

    private function quoteForTransaction(array $items): array
    {
        $normalizedItems = $this->normalizeItems($items);
        $products = $this->loadProducts(array_keys($normalizedItems), true);
        $lineItems = [];
        $subtotal = 0.0;

        foreach ($normalizedItems as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => ["Product {$productId} was not found."],
                ]);
            }

            $availableStock = (int) $product->stocks->sum('quantity');
            if ($availableStock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => ["Insufficient stock for {$product->name}."],
                ]);
            }

            $unitPrice = $this->resolveUnitPrice($product);
            $lineTotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineTotal;

            $lineItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku ?: $product->stocks->first()?->sku,
                'quantity' => $quantity,
                'available_stock' => $availableStock,
                'unit_price' => $unitPrice,
                'total_price' => $lineTotal,
                'image' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
            ];
        }

        return $this->buildTotalsPayload($lineItems, $subtotal);
    }

    private function buildTotalsPayload(array $lineItems, float $subtotal): array
    {
        $shippingCharge = self::SHIPPING_CHARGE;
        $taxAmount = round($subtotal * self::TAX_RATE, 2);
        $totalAmount = round($subtotal + $shippingCharge + $taxAmount, 2);

        return [
            'items' => $lineItems,
            'subtotal' => round($subtotal, 2),
            'discount_amount' => 0.00,
            'shipping_charge' => $shippingCharge,
            'tax_rate' => self::TAX_RATE,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ];
    }

    private function normalizeItems(array $items): array
    {
        if (! is_array($items) || $items === []) {
            throw ValidationException::withMessages([
                'items' => 'At least one cart item is required.',
            ]);
        }

        $normalized = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                throw ValidationException::withMessages([
                    "items.{$index}" => 'Each item must be an array.',
                ]);
            }

            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($productId < 1) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'The product id is required.',
                ]);
            }

            if ($quantity < 1) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => 'The quantity must be at least 1.',
                ]);
            }

            $normalized[$productId] = ($normalized[$productId] ?? 0) + $quantity;
        }

        return $normalized;
    }

    private function loadProducts(array $productIds, bool $lock = false)
    {
        $query = Product::query()
            ->with(['stocks'])
            ->whereIn('id', $productIds)
            ->whereRaw('LOWER(status) = ?', ['published']);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get()->keyBy('id');
    }

    private function normalizeShippingAddress(array $payload, User $customer): array
    {
        $firstName = trim((string) ($payload['first_name'] ?? ''));
        $lastName = trim((string) ($payload['last_name'] ?? ''));
        $name = trim((string) ($payload['name'] ?? trim($firstName . ' ' . $lastName)));

        return [
            'name' => $name !== '' ? $name : $customer->name,
            'email' => $payload['email'] ?? $customer->email,
            'phone' => $payload['phone'] ?? null,
            'address' => $payload['address'] ?? null,
            'area' => $payload['apartment'] ?? $payload['division'] ?? $payload['area'] ?? null,
            'city' => $payload['city'] ?? null,
            'zip' => $payload['zip'] ?? $payload['postal_code'] ?? null,
            'country' => $payload['country'] ?? 'Bangladesh',
            'transaction_id' => $payload['transaction_id'] ?? null,
            'payment_method' => $this->normalizePaymentMethod($payload['payment_method'] ?? null),
        ];
    }

    private function normalizePaymentMethod(mixed $paymentMethod): string
    {
        $paymentMethod = strtolower(trim((string) $paymentMethod));

        return in_array($paymentMethod, ['bkash', 'nagad', 'card', 'cod'], true)
            ? $paymentMethod
            : 'cod';
    }

    private function resolveUnitPrice(Product $product): float
    {
        if ($product->sale_price !== null && (float) $product->sale_price < (float) $product->base_price) {
            return (float) $product->sale_price;
        }

        return (float) $product->base_price;
    }

    private function reserveStock(int $productId, int $quantity, int $orderId, int $userId): void
    {
        $stocks = ProductStock::query()
            ->where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $remaining = $quantity;

        foreach ($stocks as $stock) {
            if ($remaining <= 0) {
                break;
            }

            $deduct = min($stock->quantity, $remaining);
            $stock->update(['quantity' => $stock->quantity - $deduct]);
            $remaining -= $deduct;

            StockLog::create([
                'product_id' => $productId,
                'order_id' => $orderId,
                'quantity' => $deduct,
                'change_type' => 'order',
                'note' => 'Stock reserved for customer checkout.',
                'created_by' => $userId,
                'created_at' => now(),
            ]);
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'items' => ['Unable to reserve stock for the selected items.'],
            ]);
        }
    }
}
