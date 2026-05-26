@extends('tyro-dashboard::layouts.user')

@section('title', 'Product inventory')

@section('content')
@php
    $currentQuery = request()->except('page');
    $inventoryUrl = function (array $overrides = [], array $remove = []) use ($currentQuery) {
        $query = array_merge(
            \Illuminate\Support\Arr::except($currentQuery, $remove),
            $overrides
        );

        return route('reports.stock', array_filter($query, static fn ($value) => $value !== null && $value !== ''));
    };
    $hasSidebarFilter = request()->filled('category') || request()->filled('brand') || request()->filled('variety');
    $activeStockStatus = request('stock_status', 'all');
    $inventoryValueK = number_format(((float) ($stats['inventory_value'] ?? 0)) / 1000, 1);
@endphp

<style>
    .inventory-page {
        background: #f5f5f4;
        border-radius: 12px;
        color: #111827;
        padding: 0;
    }

    .inventory-shell {
        display: grid;
        gap: 16px;
    }

    .inventory-header,
    .inventory-panel,
    .inventory-sidebar,
    .inventory-table-panel,
    .inventory-stats .stat-card {
        background: #fff;
        border: 0.5px solid rgba(0, 0, 0, 0.12);
        border-radius: 12px;
    }

    .inventory-header {
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
    }

    .inventory-title {
        margin: 0;
        font-size: 30px;
        line-height: 1.1;
        font-weight: 700;
        letter-spacing: -0.03em;
    }

    .inventory-subtitle {
        margin-top: 6px;
        color: #6b7280;
        font-size: 14px;
    }

    .inventory-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .inventory-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 40px;
        padding: 0 14px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        white-space: nowrap;
    }

    .inventory-btn svg {
        width: 16px;
        height: 16px;
    }

    .inventory-btn-primary {
        background: #185FA5;
        color: #fff;
    }

    .inventory-btn-primary:hover {
        background: #144f89;
        color: #fff;
    }

    .inventory-btn-secondary {
        background: #fff;
        border-color: rgba(0, 0, 0, 0.14);
        color: #185FA5;
    }

    .inventory-btn-secondary:hover {
        border-color: #185FA5;
        background: #f4f8fd;
        color: #185FA5;
    }

    .inventory-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
    }

    .stat-card {
        padding: 18px 20px;
    }

    .stat-label {
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .stat-value {
        margin-top: 8px;
        font-size: 30px;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }

    .stat-hint {
        margin-top: 8px;
        font-size: 13px;
        color: #6b7280;
    }

    .inventory-toolbar {
        padding: 18px 20px 14px;
        display: grid;
        gap: 14px;
    }

    .toolbar-row {
        display: grid;
        grid-template-columns: minmax(240px, 1.5fr) repeat(3, minmax(150px, 1fr)) auto;
        gap: 12px;
        align-items: end;
    }

    .field-label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #6b7280;
    }

    .field-input,
    .field-select {
        width: 100%;
        height: 42px;
        border-radius: 8px;
        border: 0.5px solid rgba(0, 0, 0, 0.12);
        background: #fff;
        padding: 0 12px;
        font-size: 14px;
        color: #111827;
        outline: none;
    }

    .field-input:focus,
    .field-select:focus {
        border-color: #185FA5;
        box-shadow: 0 0 0 3px rgba(24, 95, 165, 0.12);
    }

    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap svg {
        position: absolute;
        left: 12px;
        top: 50%;
        width: 16px;
        height: 16px;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .search-input-wrap .field-input {
        padding-left: 38px;
    }

    .toolbar-submit {
        height: 42px;
        padding: 0 16px;
        border: none;
        border-radius: 8px;
        background: #185FA5;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .toolbar-submit:hover {
        background: #144f89;
    }

    .status-tabs {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .status-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 14px;
        border-radius: 999px;
        border: 0.5px solid rgba(0, 0, 0, 0.12);
        color: #374151;
        background: #fff;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .status-tab.is-active {
        background: #185FA5;
        color: #fff;
        border-color: #185FA5;
    }

    .active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 0.5px solid rgba(0, 0, 0, 0.12);
        background: #fff;
        color: #374151;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .filter-chip button,
    .filter-chip span {
        border: none;
        background: transparent;
        color: inherit;
        font: inherit;
        padding: 0;
    }

    .filter-chip .chip-remove {
        width: 20px;
        height: 20px;
        border-radius: 999px;
        background: #f3f4f6;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .inventory-main {
        display: grid;
        grid-template-columns: 200px minmax(0, 1fr);
        gap: 16px;
        align-items: start;
    }

    .inventory-sidebar {
        padding: 16px;
        position: sticky;
        top: 16px;
    }

    .sidebar-section + .sidebar-section {
        margin-top: 18px;
    }

    .sidebar-title {
        margin: 0 0 10px;
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .pill-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .pill-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 12px;
        border-radius: 8px;
        border: 0.5px solid rgba(0, 0, 0, 0.12);
        background: #fff;
        color: #374151;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .pill-link:hover {
        border-color: #185FA5;
        color: #0C447C;
        background: #f5faff;
    }

    .pill-link.is-active {
        background: #E6F1FB;
        border-color: #185FA5;
        color: #0C447C;
    }

    .pill-count {
        color: inherit;
        opacity: 0.75;
        font-size: 12px;
        font-variant-numeric: tabular-nums;
    }

    .clear-filters {
        display: inline-flex;
        margin-top: 18px;
        color: #185FA5;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .inventory-table-panel {
        overflow: hidden;
    }

    .inventory-table-wrap {
        overflow-x: auto;
    }

    .inventory-table {
        width: 100%;
        border-collapse: collapse;
    }

    .inventory-table thead th {
        background: #f9f9f8;
        border-bottom: 0.5px solid rgba(0, 0, 0, 0.12);
        padding: 14px 16px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6b7280;
        white-space: nowrap;
        text-align: left;
    }

    .inventory-table tbody td {
        padding: 16px;
        border-bottom: 0.5px solid rgba(0, 0, 0, 0.12);
        vertical-align: top;
        background: #fff;
    }

    .inventory-table tbody tr:hover td {
        background: #f5f5f4;
    }

    .inventory-name {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .inventory-sku {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
        font-size: 12px;
        color: #6b7280;
    }

    .badge-pill {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-pill.green {
        background: #EAF3DE;
        color: #27500A;
    }

    .badge-pill.amber {
        background: #FAEEDA;
        color: #633806;
    }

    .badge-pill.red {
        background: #FCEBEB;
        color: #791F1F;
    }

    .badge-pill.gray {
        background: #f3f4f6;
        color: #374151;
    }

    .pill-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        margin: 0 6px 6px 0;
    }

    .variant-analysis-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .variant-analysis-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 12px;
        border-radius: 8px;
        border: 0.5px solid rgba(0, 0, 0, 0.12);
        background: #fff;
        color: #374151;
        font-size: 13px;
        font-weight: 600;
    }

    .variant-analysis-meta {
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
    }

    .text-mono-muted {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
        color: #6b7280;
        font-size: 12px;
    }

    .table-price {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
    }

    .table-qty {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
    }

    .row-actions {
        display: inline-flex;
        gap: 8px;
        opacity: 0;
        transition: opacity 0.15s ease;
    }

    .inventory-table tbody tr:hover .row-actions {
        opacity: 1;
    }

    .icon-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 0.5px solid rgba(0, 0, 0, 0.12);
        background: #fff;
        color: #374151;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
    }

    .icon-action:hover {
        border-color: #185FA5;
        color: #185FA5;
    }

    .icon-action svg {
        width: 16px;
        height: 16px;
    }

    .empty-state {
        text-align: center;
        color: #6b7280;
        padding: 44px 16px !important;
    }

    .table-footer {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        padding: 14px 18px;
        border-top: 0.5px solid rgba(0, 0, 0, 0.12);
        background: #fff;
    }

    .table-footer-summary {
        color: #374151;
        font-size: 13px;
        font-weight: 600;
    }

    .pagination-wrap .pagination {
        margin: 0;
    }

    .checkbox-cell {
        width: 44px;
    }

    .inventory-checkbox {
        width: 16px;
        height: 16px;
        accent-color: #185FA5;
    }

    @media (max-width: 1200px) {
        .toolbar-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .toolbar-actions {
            grid-column: 1 / -1;
        }

        .inventory-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .inventory-main {
            grid-template-columns: 1fr;
        }

        .inventory-sidebar {
            position: static;
        }
    }

    @media (max-width: 640px) {
        .inventory-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .toolbar-row,
        .inventory-stats {
            grid-template-columns: 1fr;
        }

        .table-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    /* Pagination styles: make default paginator match table footer */
    .pagination-wrap {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .pagination-wrap .pagination,
    .inventory-table .pagination {
        display: inline-flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 8px;
        align-items: center;
    }

    .pagination-wrap .pagination li {
        margin: 0;
    }

    .pagination-wrap .pagination li a,
    .pagination-wrap .pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        border-radius: 8px;
        border: 0.5px solid rgba(0, 0, 0, 0.12);
        background: #fff;
        color: #374151;
        font-weight: 700;
        text-decoration: none;
    }

    .pagination-wrap .pagination li a:hover {
        border-color: #185FA5;
        color: #185FA5;
        background: #f4f8fd;
    }

    .pagination-wrap .pagination li.active span,
    .pagination-wrap .pagination li span[aria-current] {
        background: #185FA5;
        color: #fff;
        border-color: #185FA5;
    }

    .pagination-wrap .pagination li.disabled span,
    .pagination-wrap .pagination li.disabled a {
        opacity: 0.5;
        cursor: default;
        pointer-events: none;
        background: #fff;
    }
</style>

<div class="inventory-page">
    <div class="inventory-shell">
        <header class="inventory-header">
            <div>
                <h1 class="inventory-title">Product inventory</h1>
                <div class="inventory-subtitle">Track stock, price, and product availability from one filtered view.</div>
            </div>

            <div class="inventory-actions">
                <a href="{{ $inventoryUrl(['export' => 'csv']) }}" class="inventory-btn inventory-btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                    </svg>
                    Export
                </a>
                <a href="{{ route('products.create') }}" class="inventory-btn inventory-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                    </svg>
                    + Add product
                </a>
            </div>
        </header>

        <section class="inventory-stats">
            <div class="stat-card">
                <div class="stat-label">Total products</div>
                <div class="stat-value">{{ number_format($stats['total_products'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total qty on hand</div>
                <div class="stat-value">{{ number_format($stats['total_quantity'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Sold qty</div>
                <div class="stat-value">{{ number_format($stats['sold_quantity'] ?? 0) }}</div>
                <div class="stat-hint">From order activity</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Variant types</div>
                <div class="stat-value">{{ number_format($stats['variant_types'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">In stock</div>
                <div class="stat-value">{{ number_format($stats['in_stock'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Out of stock</div>
                <div class="stat-value">{{ number_format($stats['out_of_stock'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Inventory value</div>
                <div class="stat-value">৳{{ $inventoryValueK }}k</div>
                <div class="stat-hint">Based on price × quantity</div>
            </div>
        </section>

        <section class="inventory-panel inventory-toolbar">
            <form action="{{ route('reports.stock') }}" method="GET" class="inventory-toolbar-form">
                <input type="hidden" name="stock_status" value="{{ $activeStockStatus }}">
                <div class="toolbar-row">
                    <div>
                        <label class="field-label" for="search">Search</label>
                        <div class="search-input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input id="search" name="search" class="field-input" type="search" placeholder="Search name, sku, brand" value="{{ request('search') }}">
                        </div>
                    </div>

                    <div>
                        <label class="field-label" for="category">Category</label>
                        <select id="category" name="category" class="field-select">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="field-label" for="brand">Brand</label>
                        <select id="brand" name="brand" class="field-select">
                            <option value="">All brands</option>
                            @foreach($brands as $brandItem)
                                <option value="{{ $brandItem->id }}" {{ (string) request('brand') === (string) $brandItem->id ? 'selected' : '' }}>{{ $brandItem->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="field-label" for="variety">Variety</label>
                        <select id="variety" name="variety" class="field-select">
                            <option value="">All varieties</option>
                            @foreach($varieties as $variety)
                                <option value="{{ $variety->id }}" {{ (string) request('variety') === (string) $variety->id ? 'selected' : '' }}>
                                    {{ $variety->attribute?->name }}: {{ $variety->value }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="toolbar-actions">
                        <label class="field-label" style="visibility:hidden;">Apply</label>
                        <button type="submit" class="toolbar-submit">Apply filters</button>
                    </div>
                </div>
            </form>

            <div class="status-tabs" aria-label="Stock status filter">
                <a href="{{ $inventoryUrl(['stock_status' => 'all']) }}" class="status-tab {{ $activeStockStatus === 'all' ? 'is-active' : '' }}">All</a>
                <a href="{{ $inventoryUrl(['stock_status' => 'in']) }}" class="status-tab {{ $activeStockStatus === 'in' ? 'is-active' : '' }}">In stock</a>
                <a href="{{ $inventoryUrl(['stock_status' => 'low']) }}" class="status-tab {{ $activeStockStatus === 'low' ? 'is-active' : '' }}">Low stock</a>
                <a href="{{ $inventoryUrl(['stock_status' => 'out']) }}" class="status-tab {{ $activeStockStatus === 'out' ? 'is-active' : '' }}">Out of stock</a>
            </div>

            @if($hasSidebarFilter)
                <div class="active-filters">
                    @if(request()->filled('category'))
                        @php $activeCategory = $categories->firstWhere('id', request('category')); @endphp
                        @if($activeCategory)
                            <a href="{{ $inventoryUrl([], ['category']) }}" class="filter-chip">
                                <span>{{ $activeCategory->name }}</span>
                                <span class="chip-remove">×</span>
                            </a>
                        @endif
                    @endif

                    @if(request()->filled('brand'))
                        @php $activeBrand = $brands->firstWhere('id', request('brand')); @endphp
                        @if($activeBrand)
                            <a href="{{ $inventoryUrl([], ['brand']) }}" class="filter-chip">
                                <span>{{ $activeBrand->name }}</span>
                                <span class="chip-remove">×</span>
                            </a>
                        @endif
                    @endif

                    @if(request()->filled('variety'))
                        @php $activeVariety = $varieties->firstWhere('id', request('variety')); @endphp
                        @if($activeVariety)
                            <a href="{{ $inventoryUrl([], ['variety']) }}" class="filter-chip">
                                <span>{{ $activeVariety->attribute?->name }}: {{ $activeVariety->value }}</span>
                                <span class="chip-remove">×</span>
                            </a>
                        @endif
                    @endif
                </div>
            @endif
        </section>

        <section class="inventory-main">
            <aside class="inventory-sidebar">
                <div class="sidebar-section">
                    <h2 class="sidebar-title">Category</h2>
                    <div class="pill-list">
                        @forelse($categories as $category)
                            <a href="{{ $inventoryUrl(['category' => $category->id]) }}" class="pill-link {{ request('category') && (string) request('category') === (string) $category->id ? 'is-active' : '' }}">
                                <span>{{ $category->name }}</span>
                                <span class="pill-count">{{ number_format($category->product_count ?? 0) }}</span>
                            </a>
                        @empty
                            <div class="pill-link" style="justify-content:center; color:#6b7280;">No categories</div>
                        @endforelse
                    </div>
                </div>

                <div class="sidebar-section">
                    <h2 class="sidebar-title">Brand</h2>
                    <div class="pill-list">
                        @forelse($brands as $brandItem)
                            <a href="{{ $inventoryUrl(['brand' => $brandItem->id]) }}" class="pill-link {{ request('brand') && (string) request('brand') === (string) $brandItem->id ? 'is-active' : '' }}">
                                <span>{{ $brandItem->name }}</span>
                                <span class="pill-count">{{ number_format($brandItem->product_count ?? 0) }}</span>
                            </a>
                        @empty
                            <div class="pill-link" style="justify-content:center; color:#6b7280;">No brands</div>
                        @endforelse
                    </div>
                </div>

                <a href="{{ route('reports.stock') }}" class="clear-filters">Clear all filters</a>
            </aside>

            <div class="inventory-table-panel">
                <div class="inventory-table-wrap">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th class="checkbox-cell"><input type="checkbox" class="inventory-checkbox" aria-label="Select all products"></th>
                                <th>Product name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Variant analysis</th>
                                <th>Qty on hand</th>
                                <th>Sold qty</th>
                                <th>Stock status</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                @php
                                    $productCategories = $product->categories;
                                    $primaryCategory = $productCategories->first();
                                    $extraCategories = max(0, $productCategories->count() - 1);
                                    $variantBreakdown = $product->stocks->map(function ($stock) {
                                        $variantLabel = $stock->attributeValue?->attribute?->name
                                            ? $stock->attributeValue->attribute->name . ': ' . $stock->attributeValue->value
                                            : ($stock->attributeValue?->value ?? ($stock->sku ?: 'Variant #' . $stock->id));

                                        return [
                                            'label' => $variantLabel,
                                            'qty' => (int) $stock->quantity,
                                        ];
                                    })->filter(fn ($variant) => $variant['label'] !== '');
                                    $primaryVariant = $variantBreakdown->first();
                                    $extraVariants = max(0, $variantBreakdown->count() - 1);
                                    $stockBadgeClass = $product->stock_status === 'in' ? 'green' : ($product->stock_status === 'low' ? 'amber' : 'red');
                                    $stockLabel = $product->stock_status === 'in' ? 'In stock' : ($product->stock_status === 'low' ? 'Low (' . number_format($product->qty) . ')' : 'Out of stock');
                                    $soldQty = max(0, (int) ($product->sold_qty ?? 0));
                                @endphp
                                <tr>
                                    <td class="checkbox-cell"><input type="checkbox" class="inventory-checkbox" aria-label="Select {{ $product->name }}"></td>
                                    <td>
                                        <div class="inventory-name">{{ $product->name }}</div>
                                        <div class="inventory-sku">{{ $product->sku ?? '—' }}</div>
                                    </td>
                                    <td><span class="text-mono-muted">{{ $product->sku ?? '—' }}</span></td>
                                    <td>
                                        @if($primaryCategory)
                                            <span class="pill-badge">{{ $primaryCategory->name }}</span>
                                            @if($extraCategories > 0)
                                                <span class="pill-badge">+{{ $extraCategories }}</span>
                                            @endif
                                        @else
                                            <span class="text-mono-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->brand?->name ?? '—' }}</td>
                                    <td>
                                        @if($primaryVariant)
                                            <span class="pill-badge">{{ $primaryVariant['label'] }} · {{ number_format($primaryVariant['qty']) }}</span>
                                            @if($extraVariants > 0)
                                                <span class="pill-badge">+{{ $extraVariants }}</span>
                                            @endif
                                        @else
                                            <span class="text-mono-muted">—</span>
                                        @endif
                                    </td>
                                    <td><span class="table-qty">{{ number_format((int) $product->qty) }}</span></td>
                                    <td><span class="table-qty">{{ number_format($soldQty) }}</span></td>
                                    <td><span class="badge-pill {{ $stockBadgeClass }}">{{ $stockLabel }}</span></td>
                                    <td><span class="table-price">৳{{ number_format((float) $product->price) }}</span></td>
                                    <td>
                                        <div class="row-actions">
                                            <a href="{{ route('products.edit', $product) }}" class="icon-action" aria-label="Edit {{ $product->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="icon-action" aria-label="Delete {{ $product->name }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2m-7 0h6m-9 0h12l-1 14H6L5 6z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="empty-state">No products found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
                    <div style="font-size:0.875rem; color:var(--muted-foreground);">
                        Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of
                        {{ number_format($products->total()) }} products · On hand {{ number_format($stats['total_quantity'] ?? 0) }} · Sold {{ number_format($stats['sold_quantity'] ?? 0) }}
                    </div>
                    <div class="pagination">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
