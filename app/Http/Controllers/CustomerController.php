<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->whereHas('roles', function ($q) {
                $q->where('slug', 'customer');
            })
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->withMax('orders', 'created_at');

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        $summaryQuery = User::query()->whereHas('roles', function ($q) {
            $q->where('slug', 'customer');
        });

        $summary = [
            'total_customers' => $summaryQuery->count(),
            'with_orders' => (clone $summaryQuery)->whereHas('orders')->count(),
            'new_this_month' => (clone $summaryQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'total_spent' => Order::query()->whereHas('customer', function ($q) {
                $q->whereHas('roles', function ($r) {
                    $r->where('slug', 'customer');
                });
            })->sum('total_amount'),
        ];

        $filters = $request->only(['search']);

        return view('customers.index', compact('customers', 'summary', 'filters'));
    }

    public function show(User $customer)
    {
        if (!$customer->roles()->where('slug', 'customer')->exists()) {
            abort(404);
        }

        $customer->load([
            'orders' => function ($query) {
                $query->latest()->withCount('items');
            }
        ]);

        $totalOrders = $customer->orders->count();
        $totalSpent = (float) $customer->orders->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

        $shippingAddresses = $customer->orders
            ->pluck('shipping_address')
            ->filter()
            ->unique(function ($address) {
                return data_get($address, 'phone') . '|' . data_get($address, 'address') . '|' . data_get($address, 'zip');
            })
            ->values();

        return view('customers.show', compact('customer', 'totalOrders', 'totalSpent', 'averageOrderValue', 'shippingAddresses'));
    }
}
