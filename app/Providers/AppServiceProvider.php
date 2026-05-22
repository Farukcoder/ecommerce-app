<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use HasinHayder\TyroDashboard\Support\DashboardRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share $dashboardRoute with all views so custom views that extend
        // tyro-dashboard layouts (e.g. products/index, products/create) can
        // call $dashboardRoute::name() and $dashboardRoute::pattern() helpers.
        View::share('dashboardRoute', DashboardRoute::class);

        View::composer('tyro-dashboard::dashboard.user', function ($view): void {
            $view->with('stats', $this->buildUserDashboardStats(
                $view->getData()['stats'] ?? [],
                auth()->user()
            ));
        });
    }

    /**
     * Build the dashboard data for the authenticated user.
     */
    private function buildUserDashboardStats(array $baseStats, ?User $user): array
    {
        if (! $user) {
            return array_merge($baseStats, $this->defaultUserDashboardStats());
        }

        $ordersQuery = Order::query()->where('customer_id', $user->id);

        $totalOrders = (clone $ordersQuery)->count();
        $totalSpent = (float) (clone $ordersQuery)->sum('total_amount');
        $pendingOrders = (clone $ordersQuery)->whereIn('status', ['pending', 'processing'])->count();
        $fulfilledOrders = (clone $ordersQuery)->whereIn('status', ['shipped', 'delivered'])->count();
        $refundedOrders = (clone $ordersQuery)->where('status', 'refunded')->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

        $currentMonthSpent = (float) (clone $ordersQuery)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->sum('total_amount');
        $previousMonthSpent = (float) (clone $ordersQuery)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total_amount');

        $monthGrowthPct = $previousMonthSpent > 0
            ? round((($currentMonthSpent - $previousMonthSpent) / $previousMonthSpent) * 100, 1)
            : ($currentMonthSpent > 0 ? 100.0 : 0.0);

        $weeklySales = $this->buildWeeklySalesSeries($ordersQuery);
        $recentOrders = $this->buildRecentOrders((clone $ordersQuery)->withCount('items')->latest()->take(5)->get());

        return array_merge($baseStats, [
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'pending_orders' => $pendingOrders,
            'fulfilled_orders' => $fulfilledOrders,
            'refunded_orders' => $refundedOrders,
            'average_order_value' => $averageOrderValue,
            'month_growth_pct' => $monthGrowthPct,
            'weekly_sales' => $weeklySales,
            'recent_orders' => $recentOrders,
            'recent_orders_count' => $recentOrders->count(),
        ]);
    }

    /**
     * Default dashboard data when the user is not available.
     */
    private function defaultUserDashboardStats(): array
    {
        return [
            'total_orders' => 0,
            'total_spent' => 0,
            'pending_orders' => 0,
            'fulfilled_orders' => 0,
            'refunded_orders' => 0,
            'average_order_value' => 0,
            'month_growth_pct' => 0,
            'weekly_sales' => [],
            'recent_orders' => collect(),
            'recent_orders_count' => 0,
        ];
    }

    /**
     * Build a 7-day sales series for the dashboard chart.
     */
    private function buildWeeklySalesSeries($ordersQuery): array
    {
        $weekStart = now()->startOfWeek();

        $series = collect(range(0, 6))->map(function (int $offset) use ($ordersQuery, $weekStart) {
            $date = $weekStart->copy()->addDays($offset);
            $dayOrdersQuery = (clone $ordersQuery)->whereDate('created_at', $date->toDateString());

            return [
                'label' => $date->format('D'),
                'total' => (float) (clone $dayOrdersQuery)->sum('total_amount'),
                'order_count' => (clone $dayOrdersQuery)->count(),
            ];
        });

        $maxTotal = max(1, (float) $series->max('total'));

        return $series->map(function (array $day) use ($maxTotal): array {
            $height = $day['total'] > 0
                ? max(8, (int) round(($day['total'] / $maxTotal) * 100))
                : 0;

            return [
                'label' => $day['label'],
                'total' => $day['total'],
                'formatted_total' => '$' . number_format($day['total'], 2),
                'order_count' => $day['order_count'],
                'height' => $height,
            ];
        })->all();
    }

    /**
     * Normalize recent order rows for the dashboard.
     */
    private function buildRecentOrders(Collection $orders): Collection
    {
        return $orders->map(function (Order $order): array {
            return [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
                'status_class' => $this->dashboardStatusClass($order->status),
                'payment_status' => $order->payment_status,
                'payment_label' => ucfirst(str_replace('_', ' ', $order->payment_status)),
                'payment_class' => $this->dashboardPaymentClass($order->payment_status),
                'formatted_total' => '$' . number_format((float) $order->total_amount, 2),
                'item_count' => (int) $order->items_count,
                'placed_at' => $order->created_at ? $order->created_at->format('M d, Y') : '—',
            ];
        });
    }

    private function dashboardStatusClass(string $status): string
    {
        return match ($status) {
            'delivered' => 'badge-success',
            'shipped' => 'badge-info',
            'processing' => 'badge-primary',
            'pending' => 'badge-secondary',
            'cancelled' => 'badge-danger',
            'refunded' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    private function dashboardPaymentClass(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'paid' => 'badge-success',
            'partial' => 'badge-primary',
            'refunded' => 'badge-warning',
            'unpaid' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }
}
