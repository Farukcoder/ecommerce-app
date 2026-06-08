<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;
use App\Models\HeaderSetting;
use App\Models\OrderItem;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReportData($request);

        return view('reports.sales', $data);
    }

    public function download(Request $request)
    {
        $data = $this->buildReportData($request);
        $fileName = sprintf('sales-report-%s.pdf', now()->format('Ymd-His'));

        $pdf = Pdf::loadView('reports.sales-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->output();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    private function buildReportData(Request $request): array
    {
        $status = $request->string('status')->toString();
        $range = $request->string('range')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        if ($range === '') {
            $range = 'monthly';
        }

        [$dateFrom, $dateTo] = $this->resolveRange($range, $dateFrom, $dateTo);

        $query = Order::query()
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if ($status !== '') {
            $query->where('status', $status);
        }

        $totalOrders = (clone $query)->count();
        $totalRevenue = (float) (clone $query)->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0.0;

        $headerSetting = HeaderSetting::query()->latest('id')->first();
        $systemSetting = SystemSetting::query()->latest('id')->first();

        $summary = [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'paid_orders' => (clone $query)->where('payment_status', 'paid')->count(),
            'pending_orders' => (clone $query)->where('status', 'pending')->count(),
        ];

        $orders = (clone $query)
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Order $order): array {
                return [
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer?->name ?? 'Guest',
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'items_count' => $order->items_count,
                    'total_amount' => (float) $order->total_amount,
                    'created_at' => $order->created_at,
                ];
            });

        $products = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.created_at', '>=', $dateFrom)
            ->whereDate('orders.created_at', '<=', $dateTo)
            ->when($status !== '', function ($query) use ($status): void {
                $query->where('orders.status', $status);
            })
            ->selectRaw('order_items.product_id, order_items.product_name, order_items.product_sku, SUM(order_items.quantity) as total_quantity, COUNT(DISTINCT order_items.order_id) as order_count, SUM(order_items.total_price) as total_revenue, AVG(order_items.unit_price) as avg_unit_price')
            ->groupBy('order_items.product_id', 'order_items.product_name', 'order_items.product_sku')
            ->orderByDesc('total_revenue')
            ->get();

        $daily = (clone $query)
            ->selectRaw('DATE(created_at) as order_date, COUNT(*) as order_count, SUM(total_amount) as total_revenue, AVG(total_amount) as avg_order_value')
            ->groupBy('order_date')
            ->orderByDesc('order_date')
            ->get();

        $logo = $this->getLogoPdfSource($headerSetting?->header_logo)
            ?? $this->getLogoPdfSource($systemSetting?->system_logo_black)
            ?? $this->getLogoPdfSource($systemSetting?->system_logo_white)
            ?? $headerSetting?->header_logo_url
            ?? $systemSetting?->system_logo_black_url
            ?? $systemSetting?->system_logo_white_url;

        $brand = [
            'name' => $systemSetting?->system_name ?? config('app.name'),
            'subtitle' => $systemSetting?->frontend_website_name ?? 'Sales Report',
            'logo' => $logo,
        ];

        $filters = [
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'range' => $range,
        ];

        return [
            'brand' => $brand,
            'summary' => $summary,
            'orders' => $orders,
            'products' => $products,
            'daily' => $daily,
            'filters' => $filters,
        ];
    }

    private function getLogoPdfSource(?string $storagePath): ?string
    {
        if (!$storagePath) {
            return null;
        }

        $path = public_path('storage/' . $storagePath);
        if (!file_exists($path)) {
            $path = storage_path('app/public/' . $storagePath);
        }

        if (!file_exists($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'svg') {
            $svg = file_get_contents($path);
            if (preg_match('/<style>(.*?)<\/style>/s', $svg, $matches)) {
                $styleContent = $matches[1];
                preg_match_all('/\.([a-zA-Z0-9_-]+)\s*\{(.*?)\}/s', $styleContent, $classMatches);
                $styles = [];
                for ($i = 0; $i < count($classMatches[1]); $i++) {
                    $className = $classMatches[1][$i];
                    $styleRules = trim($classMatches[2][$i]);
                    $rules = [];
                    foreach (explode(';', $styleRules) as $rule) {
                        if (trim($rule)) {
                            $parts = explode(':', $rule, 2);
                            if (count($parts) === 2) {
                                $rules[trim($parts[0])] = trim($parts[1]);
                            }
                        }
                    }
                    $styles[$className] = $rules;
                }

                $svg = preg_replace_callback('/class=["\']([a-zA-Z0-9_-]+)["\']/', function($m) use ($styles) {
                    $className = $m[1];
                    if (isset($styles[$className])) {
                        $attrs = [];
                        foreach ($styles[$className] as $k => $v) {
                            if (in_array($k, ['fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-miterlimit', 'opacity'])) {
                                $attrs[] = $k . '="' . $v . '"';
                            }
                        }
                        return implode(' ', $attrs);
                    }
                    return $m[0];
                }, $svg);
            }

            $svg = preg_replace('/<\?xml.*?\?>/', '', $svg);
            $svg = preg_replace('/<defs>.*?<\/defs>/s', '', $svg);

            return 'data:image/svg+xml;base64,' . base64_encode(trim($svg));
        }

        $data = file_get_contents($path);
        return 'data:image/' . $extension . ';base64,' . base64_encode($data);
    }

    private function resolveRange(string $range, string $dateFrom, string $dateTo): array
    {
        $today = now();

        if ($range === 'daily') {
            return [$today->toDateString(), $today->toDateString()];
        }

        if ($range === 'yearly') {
            return [$today->copy()->startOfYear()->toDateString(), $today->copy()->endOfYear()->toDateString()];
        }

        if ($range === 'monthly') {
            return [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()];
        }

        if ($dateTo === '') {
            $dateTo = $today->toDateString();
        }

        if ($dateFrom === '') {
            $dateFrom = $today->copy()->subDays(30)->toDateString();
        }

        return [$dateFrom, $dateTo];
    }
}
