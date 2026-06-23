<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Refund;
use App\Support\CurrencyFormatter;
use Illuminate\Auth\Events\Login;
use HasinHayder\TyroDashboard\Support\DashboardRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

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

        Blade::directive('money', function (string $expression): string {
            return "<?php echo \\App\\Support\\CurrencyFormatter::format({$expression}); ?>";
        });

        Blade::directive('currencySymbol', function (): string {
            return "<?php echo \\App\\Support\\CurrencyFormatter::symbol(); ?>";
        });

        View::composer('*', function ($view): void {
            $view->with('currencySymbol', CurrencyFormatter::symbol());
        });

        Event::listen(Login::class, function (Login $event): void {
            if ($event->guard !== config('auth.defaults.guard', 'web')) {
                return;
            }

            if (! $event->user instanceof User) {
                return;
            }

            if ($this->isCustomerOnlyAccount($event->user)) {
                Auth::guard($event->guard)->logout();

                throw ValidationException::withMessages([
                    'email' => 'Customer accounts must sign in through the website panel.',
                ]);
            }
        });

        View::composer('tyro-dashboard::dashboard.user', function ($view): void {
            $view->with('stats', $this->buildUserDashboardStats(
                $view->getData()['stats'] ?? [],
                Auth::user()
            ));
        });
    }

    /**
     * Build the dashboard data for the authenticated user.
     */
    private function buildUserDashboardStats(array $baseStats, ?User $user): array
    {
        $ordersQuery = Order::query();

        $totalOrders = (clone $ordersQuery)->count();
        $totalRevenue = (float) (clone $ordersQuery)->where('status', '!=', 'cancelled')->sum('total_amount');
        $pendingOrders = (clone $ordersQuery)->where('status', 'pending')->count();
        
        $totalProducts = Product::count();
        $totalCustomers = User::whereHas('roles', function ($q) {
            $q->where('slug', 'customer');
        })->count();

        $totalRefunded = (float) Refund::sum('amount');
        $availableBalance = max(0.0, $totalRevenue - $totalRefunded);

        $totalSpent = $user ? (float) Order::where('customer_id', $user->id)->sum('total_amount') : 0.0;

        $weeklySales = $this->buildWeeklySalesSeries($ordersQuery);
        $monthlySales = $this->buildMonthlySalesSeries();
        $recentOrders = $this->buildRecentOrders((clone $ordersQuery)->with('customer')->withCount('items')->latest()->take(5)->get());
        $topSelling = $this->buildTopSellingProducts();
        $lowStock = $this->buildLowStockAlerts();
        $latestReviews = $this->buildLatestReviews();
        $recentTransactions = $this->buildRecentTransactions();

        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

        $currentMonthSpent = (float) (clone $ordersQuery)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->sum('total_amount');
        $previousMonthSpent = (float) (clone $ordersQuery)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total_amount');

        $monthGrowthPct = $previousMonthSpent > 0
            ? round((($currentMonthSpent - $previousMonthSpent) / $previousMonthSpent) * 100, 1)
            : ($currentMonthSpent > 0 ? 100.0 : 0.0);

        return array_merge($baseStats, [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_spent' => $totalSpent,
            'pending_orders' => $pendingOrders,
            'total_products' => $totalProducts,
            'total_customers' => $totalCustomers,
            'available_balance' => $availableBalance,
            'month_growth_pct' => $monthGrowthPct,
            'weekly_sales' => $weeklySales,
            'monthly_sales' => $monthlySales,
            'recent_orders' => $recentOrders,
            'recent_orders_count' => $recentOrders->count(),
            'top_selling' => $topSelling,
            'low_stock' => $lowStock,
            'latest_reviews' => $latestReviews,
            'recent_transactions' => $recentTransactions,
        ]);
    }

    /**
     * Default dashboard data when the user is not available.
     */
    private function defaultUserDashboardStats(): array
    {
        return [
            'total_orders' => 0,
            'total_revenue' => 0,
            'total_spent' => 0,
            'pending_orders' => 0,
            'total_products' => 0,
            'total_customers' => 0,
            'available_balance' => 0,
            'month_growth_pct' => 0,
            'weekly_sales' => [],
            'monthly_sales' => [],
            'recent_orders' => collect(),
            'recent_orders_count' => 0,
            'top_selling' => collect(),
            'low_stock' => collect(),
            'latest_reviews' => collect(),
            'recent_transactions' => collect(),
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
                'formatted_total' => CurrencyFormatter::format($day['total']),
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
                'formatted_total' => CurrencyFormatter::format((float) $order->total_amount),
                'item_count' => (int) $order->items_count,
                'placed_at' => $order->created_at ? $order->created_at->format('M d, Y') : '—',
                'customer_name' => $order->customer?->name ?? 'Guest',
            ];
        });
    }

    /**
     * Build a 6-month sales series for the dashboard chart.
     */
    private function buildMonthlySalesSeries(): array
    {
        $series = collect(range(5, 0))->map(function (int $offset) {
            $date = now()->subMonths($offset);
            $monthOrdersQuery = Order::query()
                ->where('status', '!=', 'cancelled')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            return [
                'label' => $date->format('M'),
                'total' => (float) $monthOrdersQuery->sum('total_amount'),
                'order_count' => $monthOrdersQuery->count(),
            ];
        });

        $maxTotal = max(1.0, (float) $series->max('total'));

        return $series->map(function (array $month) use ($maxTotal): array {
            $height = $month['total'] > 0
                ? max(8, (int) round(($month['total'] / $maxTotal) * 100))
                : 0;

            return [
                'label' => $month['label'],
                'total' => $month['total'],
                'formatted_total' => CurrencyFormatter::format($month['total']),
                'order_count' => $month['order_count'],
                'height' => $height,
            ];
        })->all();
    }

    /**
     * Get top selling products globally.
     */
    private function buildTopSellingProducts(): Collection
    {
        return OrderItem::query()
            ->selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->product_id);
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name ?: ($product?->name ?? 'Unknown Product'),
                    'sku' => $product?->sku ?? 'N/A',
                    'total_qty' => (int) $item->total_qty,
                    'total_revenue' => (float) $item->total_revenue,
                    'formatted_revenue' => CurrencyFormatter::format((float) $item->total_revenue),
                    'image' => $product?->thumbnail ? asset('storage/' . $product->thumbnail) : null,
                ];
            });
    }

    /**
     * Get products with low stock (quantity <= 5).
     */
    private function buildLowStockAlerts(): Collection
    {
        return Product::with('stocks')
            ->get()
            ->filter(function ($product) {
                return $product->qty <= 5;
            })
            ->sortBy('qty')
            ->take(5)
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku ?? 'N/A',
                    'qty' => $product->qty,
                    'status' => $product->qty === 0 ? 'Out of Stock' : 'Low Stock',
                    'status_class' => $product->qty === 0 ? 'badge-danger' : 'badge-warning',
                    'image' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
                ];
            })
            ->values();
    }

    /**
     * Get mock/dynamic reviews linked to actual products.
     */
    private function buildLatestReviews(): Collection
    {
        $sampleProducts = Product::take(4)->get();
        $mockComments = [
            'Absolutely love the quality! Fits perfectly and looks great.',
            'Great performance and build quality. Highly recommended!',
            'Very comfortable and durable. Exceeded my expectations.',
            'Nice design, though shipping took a bit longer than expected.',
        ];
        $mockNames = ['Sarah Jenkins', 'David Miller', 'Emily Watson', 'James Smith'];
        $mockRatings = [5, 4, 5, 4];
        $mockTimes = ['2 hours ago', '5 hours ago', '1 day ago', '2 days ago'];

        $reviews = collect();
        for ($i = 0; $i < 4; $i++) {
            $product = $sampleProducts->get($i);
            $reviews->push([
                'customer_name' => $mockNames[$i],
                'product_name' => $product ? $product->name : 'Premium Product ' . ($i + 1),
                'rating' => $mockRatings[$i],
                'comment' => $mockComments[$i],
                'time_ago' => $mockTimes[$i],
                'avatar_letter' => substr($mockNames[$i], 0, 1),
                'product_image' => $product?->thumbnail ? asset('storage/' . $product->thumbnail) : null,
            ]);
        }
        return $reviews;
    }

    /**
     * Extract recent transactions from orders.
     */
    private function buildRecentTransactions(): Collection
    {
        return Order::with('customer')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($order) {
                $shippingAddress = is_array($order->shipping_address) 
                    ? $order->shipping_address 
                    : json_decode($order->shipping_address, true);

                $transactionId = $shippingAddress['transaction_id'] ?? null;
                if (! $transactionId) {
                    $transactionId = 'TXN-' . strtoupper(substr(md5($order->id . $order->created_at), 0, 8));
                }

                return [
                    'transaction_id' => $transactionId,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer?->name ?? 'Guest',
                    'amount' => (float) $order->total_amount,
                    'formatted_amount' => CurrencyFormatter::format((float) $order->total_amount),
                    'payment_method' => strtoupper($order->payment_method ?: 'COD'),
                    'status' => $order->payment_status === 'paid' ? 'Success' : ($order->payment_status === 'refunded' ? 'Refunded' : 'Pending'),
                    'status_class' => $order->payment_status === 'paid' ? 'badge-success' : ($order->payment_status === 'refunded' ? 'badge-warning' : 'badge-secondary'),
                    'date' => $order->created_at ? $order->created_at->format('M d, Y H:i') : '—',
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

    private function isCustomerOnlyAccount(User $user): bool
    {
        if ($this->userHasAnyAdminRole($user)) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('customer');
        }

        if (! method_exists($user, 'roles')) {
            return false;
        }

        return $user->roles()->where('slug', 'customer')->exists();
    }

    private function userHasAnyAdminRole(User $user): bool
    {
        $adminRoles = (array) config('tyro-dashboard.admin_roles', ['admin', 'super-admin']);

        if (method_exists($user, 'hasRole')) {
            foreach ($adminRoles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        if (! method_exists($user, 'roles')) {
            return false;
        }

        return $user->roles()->whereIn('slug', $adminRoles)->exists();
    }
}
