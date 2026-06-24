@extends('tyro-dashboard::layouts.user')

@section('title', __('messages.sales_report_page_title'))

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">{{ __('messages.dashboard') }}</a>
<span class="breadcrumb-separator">/</span>
<span>{{ __('messages.sales_report_page_title') }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ __('messages.sales_report_page_title') }}</h1>
            <p class="page-description">{{ __('messages.sales_report_description') }}</p>
        </div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.total_orders_report') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['total_orders'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.total_revenue_report') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">@money($summary['total_revenue'])</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.average_order') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">@money($summary['average_order_value'])</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.paid_orders') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['paid_orders'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.pending_orders_report') }}</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $summary['pending_orders'] }}</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        @php
            $hasFilters = array_filter($filters ?? [], function ($value, $key) {
                if ($key === 'range') {
                    return false;
                }

                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);
        @endphp
        <form action="{{ route('reports.sales') }}" method="GET">
            <div class="filters-bar">
                <div class="filter-group">
                    <label class="filter-label">{{ __('messages.range') }}</label>
                    <select name="range" class="form-select" style="min-width: 160px;" onchange="this.form.submit();">
                        <option value="daily" {{ ($filters['range'] ?? 'monthly') === 'daily' ? 'selected' : '' }}>{{ __('messages.daily') }}</option>
                        <option value="monthly" {{ ($filters['range'] ?? 'monthly') === 'monthly' ? 'selected' : '' }}>{{ __('messages.monthly') }}</option>
                        <option value="yearly" {{ ($filters['range'] ?? 'monthly') === 'yearly' ? 'selected' : '' }}>{{ __('messages.yearly') }}</option>
                        <option value="custom" {{ ($filters['range'] ?? 'monthly') === 'custom' ? 'selected' : '' }}>{{ __('messages.custom') }}</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">From:</label>
                    <input type="date" name="date_from" class="form-input" value="{{ $filters['date_from'] }}" onchange="document.getElementById('range-input').value='custom';">
                </div>
                <div class="filter-group">
                    <label class="filter-label">To:</label>
                    <input type="date" name="date_to" class="form-input" value="{{ $filters['date_to'] }}" onchange="document.getElementById('range-input').value='custom';">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status:</label>
                    <select name="status" class="form-select" style="min-width: 160px;">
                        <option value="">All</option>
                        @foreach(['pending','processing','shipped','delivered','cancelled','refunded'] as $status)
                            <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">{{ __('messages.filter') }}</button>
                @if($hasFilters)
                    <a href="{{ route('reports.sales') }}" class="btn btn-ghost">{{ __('messages.clear') }}</a>
                @endif
                <a href="{{ route('reports.sales.pdf', request()->query()) }}" class="btn btn-secondary">
                    {{ __('messages.download_pdf') }}
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    @if($daily->count())
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.orders') }}</th>
                    <th>{{ __('messages.total_revenue_report') }}</th>
                    <th>{{ __('messages.average_order') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daily as $row)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($row->order_date)->format('M d, Y') }}</td>
                    <td>{{ $row->order_count }}</td>
                    <td style="font-weight:600;">@money((float) $row->total_revenue)</td>
                    <td>@money((float) $row->avg_order_value)</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align:center; color:var(--muted-foreground); padding:2rem;">
        {{ __('messages.no_sales_found') }}
    </div>
    @endif
</div>
@endsection
