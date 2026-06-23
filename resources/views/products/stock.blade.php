@extends('tyro-dashboard::layouts.user')

@section('title', 'Product inventory')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Product inventory</span>
@endsection

@section('content')
@php
    $currentQuery = request()->except('page');
    $inventoryUrl = function (array $overrides = [], array $remove = []) use ($currentQuery) {
        $query = array_merge(
            \Illuminate\Support\Arr::except($currentQuery, $remove),
            $overrides
        );

        return route('products.stock', array_filter($query, static fn ($value) => $value !== null && $value !== ''));
    };
    $hasSidebarFilter = request()->filled('category') || request()->filled('brand') || request()->filled('variety');
    $activeStockStatus = request('stock_status', 'all');
    $inventoryValueK = number_format(((float) ($stats['inventory_value'] ?? 0)) / 1000, 1);
@endphp

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Product inventory</h1>
            <p class="page-description">Track stock, price, and product availability from one filtered view.</p>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <a href="{{ $inventoryUrl(['export' => 'csv']) }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                </svg>
                Export
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Product
            </a>
        </div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V11" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Products</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ number_format($stats['total_products'] ?? 0) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Qty On Hand</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ number_format($stats['total_quantity'] ?? 0) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.29 3.86l-8.14 14A2 2 0 003.86 21h16.28a2 2 0 001.71-3.14l-8.14-14a2 2 0 00-3.42 0z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Sold Qty</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ number_format($stats['sold_quantity'] ?? 0) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5l7 7v5a2 2 0 01-2 2h-5L7 7V3z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Variant Types</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ number_format($stats['variant_types'] ?? 0) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">In Stock</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ number_format($stats['in_stock'] ?? 0) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.29 3.86l-8.14 14A2 2 0 003.86 21h16.28a2 2 0 001.71-3.14l-8.14-14a2 2 0 00-3.42 0z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Out Of Stock</div>
            <div class="stat-value" style="font-size:1.5rem;">{{ number_format($stats['out_of_stock'] ?? 0) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.5 0-4 1.2-4 3s1.5 3 4 3 4 1.2 4 3-1.5 3-4 3m0-16v2m0 14v2" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Inventory Value</div>
            <div class="stat-value" style="font-size:1.5rem;">@currencySymbol{{ $inventoryValueK }}k</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body">
        <form action="{{ route('products.stock') }}" method="GET">
            <div class="filters-bar">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" id="search" name="search" class="form-input" placeholder="Search products by name, SKU, or brand…" value="{{ request('search') }}">
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="category">Category:</label>
                    <select id="category" name="category" class="form-select" style="min-width: 160px;">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }} ({{ number_format($category->product_count ?? 0) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="brand">Brand:</label>
                    <select id="brand" name="brand" class="form-select" style="min-width: 160px;">
                        <option value="">All brands</option>
                        @foreach($brands as $brandItem)
                            <option value="{{ $brandItem->id }}" {{ (string) request('brand') === (string) $brandItem->id ? 'selected' : '' }}>{{ $brandItem->name }} ({{ number_format($brandItem->product_count ?? 0) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group" style="min-width: 190px;">
                    <label class="filter-label" for="attribute_select">Attribute:</label>
                    @php
                        $attributes = [];
                        foreach ($varieties as $v) {
                            if ($v->attribute) {
                                $attributes[$v->attribute->id] = $v->attribute->name;
                            }
                        }
                        $selectedVariety = $varieties->firstWhere('id', request('variety'));
                        $selectedAttributeId = $selectedVariety?->attribute?->id ?? '';
                    @endphp
                    <select id="attribute_select" class="form-select" style="min-width:160px;">
                        <option value="">All attributes</option>
                        @foreach($attributes as $attrId => $attrName)
                            <option value="{{ $attrId }}" {{ (string) $selectedAttributeId === (string) $attrId ? 'selected' : '' }}>{{ $attrName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group" style="min-width: 190px;">
                    <label class="filter-label" for="variety">Value:</label>
                    <select id="variety" name="variety" class="form-select" style="min-width: 190px;">
                        <option value="">All varieties</option>
                        @foreach($varieties as $variety)
                            <option data-attribute-id="{{ $variety->attribute?->id }}" value="{{ $variety->id }}" {{ (string) request('variety') === (string) $variety->id ? 'selected' : '' }}>
                                {{ $variety->attribute?->name }}: {{ $variety->value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <script>
                    (function(){
                        const attributeSelect = document.getElementById('attribute_select');
                        const varietySelect = document.getElementById('variety');
                        const allOptions = Array.from(varietySelect.querySelectorAll('option'));

                        function rebuildOptions() {
                            const attr = attributeSelect.value;
                            const prev = varietySelect.value;
                            varietySelect.innerHTML = '';
                            const defaultOpt = document.createElement('option');
                            defaultOpt.value = '';
                            defaultOpt.textContent = 'All varieties';
                            varietySelect.appendChild(defaultOpt);

                            allOptions.forEach(opt => {
                                const optAttr = opt.getAttribute('data-attribute-id') || '';
                                if (!attr || attr === optAttr) {
                                    varietySelect.appendChild(opt.cloneNode(true));
                                }
                            });

                            if (prev && Array.from(varietySelect.options).some(o => o.value === prev)) {
                                varietySelect.value = prev;
                            }
                        }

                        attributeSelect.addEventListener('change', rebuildOptions);
                        document.addEventListener('DOMContentLoaded', rebuildOptions);
                    })();
                </script>

                <div class="filter-group">
                    <label class="filter-label" for="stock_status">Stock status:</label>
                    <select id="stock_status" name="stock_status" class="form-select" style="min-width:160px;">
                        <option value="all" {{ $activeStockStatus === 'all' ? 'selected' : '' }}>All</option>
                        <option value="in" {{ $activeStockStatus === 'in' ? 'selected' : '' }}>In stock</option>
                        <option value="low" {{ $activeStockStatus === 'low' ? 'selected' : '' }}>Low stock</option>
                        <option value="out" {{ $activeStockStatus === 'out' ? 'selected' : '' }}>Out of stock</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    Apply
                </button>

                @if($hasSidebarFilter || request()->filled('search'))
                    <a href="{{ route('products.stock') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>



        @if($hasSidebarFilter)
            <div class="badge-list" style="margin-top: 1rem;">
                @if(request()->filled('category'))
                    @php $activeCategory = $categories->firstWhere('id', request('category')); @endphp
                    @if($activeCategory)
                        <a href="{{ $inventoryUrl([], ['category']) }}" class="badge badge-secondary">{{ $activeCategory->name }} ×</a>
                    @endif
                @endif

                @if(request()->filled('brand'))
                    @php $activeBrand = $brands->firstWhere('id', request('brand')); @endphp
                    @if($activeBrand)
                        <a href="{{ $inventoryUrl([], ['brand']) }}" class="badge badge-secondary">{{ $activeBrand->name }} ×</a>
                    @endif
                @endif

                @if(request()->filled('variety'))
                    @php $activeVariety = $varieties->firstWhere('id', request('variety')); @endphp
                    @if($activeVariety)
                        <a href="{{ $inventoryUrl([], ['variety']) }}" class="badge badge-secondary">{{ $activeVariety->attribute?->name }}: {{ $activeVariety->value }} ×</a>
                    @endif
                @endif
            </div>
        @endif
    </div>
</div>

<div class="card">
    @if($products->count())
        <div class="table-container">
            <table class="table" id="stock-table">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Variant Analysis</th>
                        <th>Qty On Hand</th>
                        <th>Sold Qty</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th style="text-align:right;">Actions</th>
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
                            $stockBadgeClass = $product->stock_status === 'in' ? 'badge-success' : ($product->stock_status === 'low' ? 'badge-warning' : 'badge-secondary');
                            $stockLabel = $product->stock_status === 'in' ? 'In stock' : ($product->stock_status === 'low' ? 'Low (' . number_format($product->qty) . ')' : 'Out of stock');
                            $soldQty = max(0, (int) ($product->sold_qty ?? 0));
                        @endphp
                        <tr>
                            <td>{{ $products->firstItem() + $loop->index }}</td>
                            <td>
                                <div style="font-weight:600; color:var(--foreground);">
                                    {{ \Illuminate\Support\Str::limit($product->name, 20, ' ...') }}
                                </div>
                            </td>
                            <td>
                                @if($product->sku)
                                    <code style="font-size:0.8125rem; background:var(--muted); padding:2px 6px; border-radius:4px; color:var(--muted-foreground);">{{ $product->sku }}</code>
                                @else
                                    <span style="color:var(--muted-foreground); font-size:0.8125rem;">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="badge-list">
                                    @if($primaryCategory)
                                        <span class="badge badge-secondary">{{ $primaryCategory->name }}</span>
                                        @if($extraCategories > 0)
                                            <span class="badge badge-secondary">+{{ $extraCategories }}</span>
                                        @endif
                                    @else
                                        <span style="color:var(--muted-foreground); font-size:0.8125rem;">—</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($product->brand)
                                    <span style="font-size:0.875rem; color:var(--foreground);">{{ $product->brand->name }}</span>
                                @else
                                    <span style="color:var(--muted-foreground); font-size:0.8125rem;">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="badge-list">
                                    @if($primaryVariant)
                                        <span class="badge badge-secondary">{{ $primaryVariant['label'] }} · {{ number_format($primaryVariant['qty']) }}</span>
                                        @if($extraVariants > 0)
                                            <span class="badge badge-secondary">+{{ $extraVariants }}</span>
                                        @endif
                                    @else
                                        <span style="color:var(--muted-foreground); font-size:0.8125rem;">—</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600; color:var(--foreground);">{{ number_format((int) $product->qty) }}</div>
                            </td>
                            <td>
                                <div style="font-weight:600; color:var(--foreground);">{{ number_format($soldQty) }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $stockBadgeClass }}">{{ $stockLabel }}</span>
                            </td>
                            <td>
                                <div style="font-weight:600; color:var(--foreground);">@money((float) $product->price)</div>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:0.5rem; flex-wrap:wrap; justify-content:flex-end;">
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-secondary">Edit</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background:color-mix(in srgb, var(--destructive), transparent 88%); color:var(--destructive);">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center; color:var(--muted-foreground); padding:2rem;">No products found for the selected filters.</td>
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
    @else
        <div style="padding:2rem; text-align:center; color:var(--muted-foreground);">No products found for the selected filters.</div>
    @endif
</div>
@endsection
