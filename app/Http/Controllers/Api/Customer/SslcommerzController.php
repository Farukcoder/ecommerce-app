<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderPlaced;
use App\Services\CustomerCheckoutService;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SslcommerzController extends Controller
{
    public function __construct(private readonly CustomerCheckoutService $checkoutService) {}

    public function checkout(Request $request): JsonResponse
    {
        $customer = $this->authorizeCustomer($request);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'apartment' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:50'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['payment_method'] = 'card';

        $order = $this->checkoutService->placeOrder($customer, $data);

        $shipping = $order->shipping_address ?? [];
        $customerName = $shipping['name'] ?? $customer->name ?? 'Customer';

        $response = Sslcommerz::setOrder($order->total_amount, $order->order_number, 'Order '.$order->order_number)
            ->setCustomer(
                $customerName,
                $shipping['email'] ?? $customer->email,
                $shipping['phone'] ?? $customer->phone,
                $shipping['address'] ?? '',
                $shipping['city'] ?? '',
                $shipping['area'] ?? $shipping['division'] ?? '',
                $shipping['zip'] ?? '',
                $shipping['country'] ?? 'Bangladesh'
            )
            ->setShippingInfo(
                $order->items->sum('quantity'),
                $shipping['address'] ?? '',
                $customerName,
                $shipping['city'] ?? '',
                $shipping['area'] ?? $shipping['division'] ?? '',
                $shipping['zip'] ?? '',
                $shipping['country'] ?? 'Bangladesh'
            )
            ->makePayment();

        if (! $response->success() || ! ($response->gatewayPageURL() || $response->redirectGatewayURL())) {
            Log::error('SSLCommerz payment initiation failed', [
                'order_id' => $order->id,
                'response' => $response->toArray(),
            ]);

            return response()->json([
                'message' => 'Unable to initiate SSLCommerz payment. Please try again.',
            ], 500);
        }

        return response()->json([
            'gateway_url' => $response->gatewayPageURL() ?? $response->redirectGatewayURL(),
            'order_id' => $order->id,
        ], 201);
    }

    public function success(Request $request)
    {
        $transactionId = (string) $request->input('tran_id');
        $order = Order::where('order_number', $transactionId)->first();

        if (! $order) {
            return view('sslcommerz.result', [
                'status' => 'error',
                'title' => 'Order not found',
                'message' => 'We could not find your order. Please contact support if this issue persists.',
                'buttonUrl' => config('app.url', url('/')),
                'buttonText' => 'Continue Shopping',
            ]);
        }

        if ($order->payment_status === 'paid') {
            return view('sslcommerz.result', [
                'status' => 'success',
                'title' => 'Payment already recorded',
                'message' => 'Your payment has already been processed successfully.',
                'buttonUrl' => config('app.url', url('/')),
                'buttonText' => 'Continue Shopping',
            ]);
        }

        if (! Sslcommerz::validatePayment($request->all(), $transactionId, $order->total_amount)) {
            return view('sslcommerz.result', [
                'status' => 'error',
                'title' => 'Payment validation failed',
                'message' => 'SSLCommerz could not validate the payment. Please contact support.',
                'buttonUrl' => config('app.url', url('/')),
                'buttonText' => 'Continue Shopping',
            ]);
        }

        $shipping = $order->shipping_address ?? [];
        $shipping['transaction_id'] = $request->input('bank_tran_id') ?? $shipping['transaction_id'] ?? null;

        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
            'shipping_address' => $shipping,
        ]);

        $order->customer?->notify(new OrderPlaced($order));

        return view('sslcommerz.result', [
            'status' => 'success',
            'title' => 'Payment Successful',
            'message' => 'Your order has been confirmed and payment was successful. Thank you for shopping with us!',
            'buttonUrl' => config('app.url', url('/')),
            'buttonText' => 'Continue Shopping',
        ]);
    }

    public function failure(Request $request)
    {
        return view('sslcommerz.result', [
            'status' => 'error',
            'title' => 'Payment Failed',
            'message' => 'The payment was not completed. Please try again or choose another payment method.',
            'buttonUrl' => config('app.url', url('/')),
            'buttonText' => 'Continue Shopping',
        ]);
    }

    public function cancel(Request $request)
    {
        return view('sslcommerz.result', [
            'status' => 'warning',
            'title' => 'Payment Cancelled',
            'message' => 'You cancelled the payment process. You can continue shopping or try again later.',
            'buttonUrl' => config('app.url', url('/')),
            'buttonText' => 'Continue Shopping',
        ]);
    }

    public function ipn(Request $request)
    {
        if (! Sslcommerz::verifyHash($request->all())) {
            return response('Invalid hash.', 400);
        }

        return response('IPN received.', 200);
    }

    private function authorizeCustomer(Request $request): User
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
