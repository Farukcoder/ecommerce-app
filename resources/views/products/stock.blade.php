@extends('tyro-dashboard::layouts.user')

@section('title', __('messages.product_inventory_page_title'))

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">{{ __('messages.dashboard') }}</a>
<span class="breadcrumb-separator">/</span>
<span>{{ __('messages.product_inventory_page_title') }}</span>
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
            <h1 class="page-title">{{ __('messages.product_inventory_page_title') }}</h1>
            <p class="page-description">{{ __('messages.product_inventory_description') }}</p>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <a href="{{ $inventoryUrl(['export' => 'csv']) }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                </svg>
                {{ __('messages.export') }}
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ __('messages.add_product') }}
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
            <div class="stat-label">{{ __('messages.total_products') }}</div>
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
            <div class="stat-label">{{ __('messages.qty_on_hand') }}</div>
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
            <div class="stat-label">{{ __('messages.sold_qty') }}</div>
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
            <div class="stat-label">{{ __('messages.variant_types') }}</div>
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
            <div class="stat-label">{{ __('messages.in_stock') }}</div>
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
            <div class="stat-label">{{ __('messages.out_of_stock') }}</div>
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
            <div class="stat-label">{{ __('messages.inventory_value') }}</div>
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
                    <input type="search" id="search" name="search" class="form-input" placeholder="{{ __('messages.search_products_placeholder') }}" value="{{ request('search') }}">
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="category">{{ __('messages.category') }}:</label>
                    <select id="category" name="category" class="form-select" style="min-width: 160px;">
                        <option value="">{{ __('messages.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }} ({{ number_format($category->product_count ?? 0) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="brand">{{ __('messages.brand') }}:</label>
                    <select id="brand" name="brand" class="form-select" style="min-width: 160px;">
                        <option value="">{{ __('messages.all_brands') }}</option>
                        @foreach($brands as $brandItem)
                            <option value="{{ $brandItem->id }}" {{ (string) request('brand') === (string) $brandItem->id ? 'selected' : '' }}>{{ $brandItem->name }} ({{ number_format($brandItem->product_count ?? 0) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group" style="min-width: 190px;">
                    <label class="filter-label" for="attribute_select">{{ __('messages.attribute') }}:</label>
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
                        <option value="">{{ __('messages.all_attributes') }}</option>
                        @foreach($attributes as $attrId => $attrName)
                            <option value="{{ $attrId }}" {{ (string) $selectedAttributeId === (string) $attrId ? 'selected' : '' }}>{{ $attrName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group" style="min-width: 190px;">
                    <label class="filter-label" for="variety">{{ __('messages.value') }}:</label>
                    <select id="variety" name="variety" class="form-select" style="min-width: 190px;">
                        <option value="">{{ __('messages.all_varieties') }}</option>
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
                            defaultOpt.textContent = '{{ __('messages.all_varieties') }}';
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
                    <label class="filter-label" for="stock_status">{{ __('messages.stock_status') }}</label>
                    <select id="stock_status" name="stock_status" class="form-select" style="min-width:160px;">
                        <option value="all" {{ $activeStockStatus === 'all' ? 'selected' : '' }}>{{ __('messages.all') }}</option>
                        <option value="in" {{ $activeStockStatus === 'in' ? 'selected' : '' }}>{{ __('messages.in_stock') }}</option>
                        <option value="low" {{ $activeStockStatus === 'low' ? 'selected' : '' }}>{{ __('messages.low_stock') }}</option>
                        <option value="out" {{ $activeStockStatus === 'out' ? 'selected' : '' }}>{{ __('messages.out_of_stock') }}</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    {{ __('messages.apply') }}
                </button>

                @if($hasSidebarFilter || request()->filled('search'))
                    <a href="{{ route('products.stock') }}" class="btn btn-ghost">{{ __('messages.clear') }}</a>
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
                        <th>{{ __('messages.sl') }}</th>
                        <th>{{ __('messages.product_name') }}</th>
                        <th>SKU</th>
                        <th>{{ __('messages.category') }}</th>
                        <th>{{ __('messages.brand') }}</th>
                        <th>{{ __('messages.variant_analysis') }}</th>
                        <th>{{ __('messages.qty_on_hand') }}</th>
                        <th>{{ __('messages.sold_qty') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.price') }}</th>
                        <th style="text-align:right;">{{ __('messages.actions') }}</th>
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
                            $stockLabel = $product->stock_status === 'in' ? __('messages.in_stock') : ($product->stock_status === 'low' ? __('messages.low_stock') . ' (' . number_format($product->qty) . ')' : __('messages.out_of_stock'));
                            $soldQty = max(0, (int) ($product->sold_qty ?? 0));
                        @endphp
                        <tr style="cursor:pointer;" onclick="openStockModal('{{ $product->uuid }}')">
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
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-secondary">{{ __('messages.edit') }}</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_product_confirm') }}');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background:color-mix(in srgb, var(--destructive), transparent 88%); color:var(--destructive);">{{ __('messages.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center; color:var(--muted-foreground); padding:2rem;">{{ __('messages.no_products_found_filters') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
            <div style="font-size:0.875rem; color:var(--muted-foreground);">
                {{ __('messages.showing') }} {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} {{ __('messages.of') }}
                {{ number_format($products->total()) }} {{ __('messages.products') }} · {{ __('messages.on_hand') }} {{ number_format($stats['total_quantity'] ?? 0) }} · {{ __('messages.sold') }} {{ number_format($stats['sold_quantity'] ?? 0) }}
            </div>
            <div class="pagination">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    @else
        <div style="padding:2rem; text-align:center; color:var(--muted-foreground);">{{ __('messages.no_products_found_filters') }}</div>
    @endif
</div>

<!-- Stock Modal -->
<div id="stock-modal" class="modal-overlay">
    <div class="modal-container" style="max-width: 850px;">
        <button type="button" class="modal-close" onclick="closeStockModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="modal-body">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <h2 class="modal-title" style="font-size: 1.25rem; margin: 0;">{{ __('messages.stock_details') }}</h2>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.875rem; color: var(--muted-foreground);">{{ __('messages.current_stock') }}:</span>
                    <span style="font-size: 1.5rem; font-weight: 700; color: var(--primary);" id="modal-current-stock">0</span>
                </div>
            </div>

            <!-- Product Info Bar -->
            <div style="background: var(--muted); border-radius: 0.5rem; padding: 0.75rem 1rem; margin-bottom: 1rem; border: 1px solid var(--border);">
                <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 1.5rem; align-items: center;">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-bottom: 0.125rem;">{{ __('messages.product_name') }}</div>
                        <div style="font-weight: 600; color: var(--foreground); font-size: 0.9rem;" id="modal-product-name">—</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-bottom: 0.125rem;">SKU</div>
                        <div style="font-weight: 600; color: var(--foreground); font-size: 0.875rem;" id="modal-product-sku">—</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-bottom: 0.125rem;">{{ __('messages.price') }}</div>
                        <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;" id="modal-product-price-display">—</div>
                    </div>
                </div>
                <input type="hidden" id="modal-product-id" value="">
            </div>

            <!-- Two Column Layout -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <!-- Left Column: Existing Variants (Read-Only) -->
                <div>
                    <h3 style="margin: 0 0 0.75rem 0; font-size: 0.875rem; font-weight: 600; color: var(--foreground);">
                        {{ __('messages.existing_variants_stock') }}
                    </h3>
                    <div id="modal-variants-list" style="max-height: 350px; overflow-y: auto; border: 1px solid var(--border); border-radius: 0.5rem;">
                        <!-- Existing variants will be loaded here (read-only) -->
                    </div>
                </div>

                <!-- Right Column: Add New Stock Form -->
                <div>
                    <h3 style="margin: 0 0 0.75rem 0; font-size: 0.875rem; font-weight: 600; color: var(--foreground);">
                        {{ __('messages.add_new_stock') }}
                    </h3>
                    <div style="background: var(--muted); border-radius: 0.5rem; padding: 1rem; border: 1px solid var(--border);">
                    <div style="margin-bottom: 0.75rem;">
                            <label style="display: block; margin-bottom: 0.25rem; font-size: 0.75rem; font-weight: 500; color: var(--muted-foreground);">
                                {{ __('messages.attribute_value') }}
                                <span id="variant-helper" style="display: none; margin-left: 0.5rem; font-size: 0.7rem; color: var(--primary); font-weight: normal;">
                                    ({{ __('messages.choose_different_variant') }})
                                </span>
                            </label>
                            <select id="new-stock-attribute" class="form-input" style="width: 100%; font-size: 0.875rem; padding: 0.5rem;">
                                <option value="">{{ __('messages.select_attribute') }}</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 0.75rem;">
                            <label style="display: block; margin-bottom: 0.25rem; font-size: 0.75rem; font-weight: 500; color: var(--muted-foreground);">{{ __('messages.sku') }}</label>
                            <input type="text" id="new-stock-sku" class="form-input" style="width: 100%; font-size: 0.875rem; padding: 0.5rem;" placeholder="{{ __('messages.enter_sku') }}">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.75rem; font-weight: 500; color: var(--muted-foreground);">{{ __('messages.quantity') }}</label>
                                <input type="number" id="new-stock-quantity" class="form-input" style="width: 100%; font-size: 0.875rem; padding: 0.5rem;" min="0" placeholder="0">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.75rem; font-weight: 500; color: var(--muted-foreground);">{{ __('messages.price') }}</label>
                                <input type="number" id="new-stock-price" class="form-input" style="width: 100%; font-size: 0.875rem; padding: 0.5rem;" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.75rem; font-weight: 500; color: var(--muted-foreground);">{{ __('messages.date') }}</label>
                                <input type="date" id="new-stock-date" class="form-input" style="width: 100%; font-size: 0.875rem; padding: 0.5rem;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.75rem; font-weight: 500; color: var(--muted-foreground);">{{ __('messages.note') }}</label>
                                <input type="text" id="new-stock-note" class="form-input" style="width: 100%; font-size: 0.875rem; padding: 0.5rem;" placeholder="{{ __('messages.note') }}">
                            </div>
                        </div>
                        <button type="button" onclick="submitNewStock()" class="btn btn-primary" style="width: 100%; padding: 0.625rem; font-size: 0.875rem;">{{ __('messages.add_stock') }}</button>
                    </div>
                </div>
            </div>

            <!-- Stock History -->
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                <h3 style="margin: 0 0 0.75rem 0; font-size: 0.875rem; font-weight: 600; color: var(--foreground);">
                    {{ __('messages.stock_history') }}
                </h3>
                <div id="modal-stock-history" style="max-height: 120px; overflow-y: auto; border: 1px solid var(--border); border-radius: 0.5rem;">
                    <!-- Stock history will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #stock-modal.modal-overlay {
        opacity: 0;
        visibility: hidden;
    }

    #stock-modal.modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    #stock-modal .modal-container {
        transform: scale(0.95) translateY(10px);
        transition: transform 0.2s ease;
    }

    #stock-modal.modal-overlay.active .modal-container {
        transform: scale(1) translateY(0);
    }

    #stock-modal .form-input {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border);
        border-radius: 0.375rem;
        background: var(--background);
        color: var(--foreground);
        font-size: 0.875rem;
        transition: border-color 0.15s ease;
    }

    #stock-modal .form-input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .variant-row {
        background: var(--muted);
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }

    .variant-row:last-child {
        margin-bottom: 0;
    }

    .variant-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .variant-name {
        font-weight: 600;
        color: var(--foreground);
        font-size: 0.875rem;
    }

    .variant-sku {
        font-size: 0.75rem;
        color: var(--muted-foreground);
        background: var(--background);
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        border: 1px solid var(--border);
    }

    .variant-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.75rem;
    }

    .variant-input-group label {
        display: block;
        margin-bottom: 0.25rem;
        font-size: 0.75rem;
        color: var(--muted-foreground);
        font-weight: 500;
    }

    .variant-input-group input {
        width: 100%;
        padding: 0.375rem 0.5rem;
        border: 1px solid var(--border);
        border-radius: 0.25rem;
        background: var(--background);
        color: var(--foreground);
        font-size: 0.8125rem;
    }

    .variant-input-group input:focus {
        outline: none;
        border-color: var(--primary);
    }
</style>

<script>
    function openStockModal(productId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(`{{ route('products.stock.details', ':product') }}`.replace(':product', productId), {
            headers: { 'X-CSRF-TOKEN': csrfToken },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ✅ Safe setter helper
                const setText = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = val;
                    else console.warn('MISSING element: ' + id);
                };
                const setVal = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val;
                    else console.warn('MISSING element: ' + id);
                };

                setVal('modal-product-id', data.product.id);
                setText('modal-product-name', data.product.name);
                setText('modal-product-sku', data.product.sku || '—');
                setText('modal-product-price-display', data.product.price.toFixed(2));
                setText('modal-current-stock', data.current_stock);
                setText('modal-current-price', data.product.price.toFixed(2));
                setVal('modal-price', data.product.price);
                setVal('modal-date', new Date().toISOString().split('T')[0]);

                // Stock history
                const historyEl = document.getElementById('modal-stock-history');
                if (historyEl) {
                    if (data.stock_history.length > 0) {
                        let historyHtml = '<table style="width:100%; border-collapse:collapse;"><thead><tr><th style="padding:0.5rem; text-align:left; border-bottom:1px solid var(--border); font-size:0.75rem; color:var(--muted-foreground);">{{ __('messages.date') }}</th><th style="padding:0.5rem; text-align:left; border-bottom:1px solid var(--border); font-size:0.75rem; color:var(--muted-foreground);">{{ __('messages.quantity') }}</th><th style="padding:0.5rem; text-align:left; border-bottom:1px solid var(--border); font-size:0.75rem; color:var(--muted-foreground);">{{ __('messages.type') }}</th></tr></thead><tbody>';
                        data.stock_history.forEach(log => {
                            historyHtml += `<tr>
                                <td style="padding:0.5rem; border-bottom:1px solid var(--border);">${log.date}</td>
                                <td style="padding:0.5rem; border-bottom:1px solid var(--border);">${log.quantity}</td>
                                <td style="padding:0.5rem; border-bottom:1px solid var(--border);">${log.type}</td>
                            </tr>`;
                        });
                        historyHtml += '</tbody></table>';
                        historyEl.innerHTML = historyHtml;
                    } else {
                        historyEl.innerHTML = '<p style="color:var(--muted-foreground);">{{ __('messages.no_stock_history') }}</p>';
                    }
                }

                // Variants (read-only display)
                const variantsEl = document.getElementById('modal-variants-list');
                if (variantsEl) {
                    if (data.variants.length > 0) {
                        let variantsHtml = '<table style="width:100%; border-collapse:collapse;"><thead><tr style="background:var(--muted);"><th style="padding:0.75rem 0.5rem; text-align:left; font-size:0.75rem; font-weight:600; color:var(--foreground);">{{ __('messages.attribute') }}</th><th style="padding:0.75rem 0.5rem; text-align:left; font-size:0.75rem; font-weight:600; color:var(--foreground);">{{ __('messages.value') }}</th><th style="padding:0.75rem 0.5rem; text-align:left; font-size:0.75rem; font-weight:600; color:var(--foreground);">SKU</th><th style="padding:0.75rem 0.5rem; text-align:right; font-size:0.75rem; font-weight:600; color:var(--foreground);">{{ __('messages.quantity') }}</th></tr></thead><tbody>';
                        data.variants.forEach(variant => {
                            // Parse variant name to extract attribute and value (format: "Attribute: Value")
                            const parts = variant.name.split(': ');
                            const attrName = parts.length > 1 ? parts[0] : '—';
                            const attrValue = parts.length > 1 ? parts.slice(1).join(': ') : variant.name;

                            variantsHtml += `<tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:0.75rem 0.5rem; font-weight:500; color:var(--foreground); font-size:0.875rem;">${attrName}</td>
                                <td style="padding:0.75rem 0.5rem; font-weight:600; color:var(--primary); font-size:0.875rem;">${attrValue}</td>
                                <td style="padding:0.75rem 0.5rem; color:var(--muted-foreground); font-family:monospace; font-size:0.8rem;">${variant.sku || '—'}</td>
                                <td style="padding:0.75rem 0.5rem; text-align:right; font-weight:600; color:var(--foreground);">${variant.quantity}</td>
                            </tr>`;
                        });
                        variantsHtml += '</tbody></table>';
                        variantsEl.innerHTML = variantsHtml;
                    } else {
                        variantsEl.innerHTML = '<div style="padding:2rem; text-align:center; color:var(--muted-foreground);">{{ __('messages.no_variants') }}</div>';
                    }
                }

                // Populate attribute options from product model
                populateAttributeOptions(data.attribute_values, data.variants);

                // Open modal
                const modal = document.getElementById('stock-modal');
                if (modal) modal.classList.add('active');

            } else {
                alert(data.message || 'Error loading stock details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading stock details');
        });
    }

    function closeStockModal() {
        document.getElementById('stock-modal').classList.remove('active');
    }

    function populateAttributeOptions(attributeValues, existingVariants) {
        const attrSelect = document.getElementById('new-stock-attribute');
        const variantHelper = document.getElementById('variant-helper');
        if (!attrSelect) {
            return;
        }

        attrSelect.innerHTML = '<option value="">{{ __('messages.select_attribute') }}</option>';

        // Show helper message if variants exist
        if (variantHelper) {
            variantHelper.style.display = existingVariants.length > 0 ? 'inline' : 'none';
        }

        // Show all available attributes
        // User can choose any variant, including new ones not yet in stock
        if (attributeValues.length === 0) {
            attrSelect.disabled = true;
        } else {
            attributeValues.forEach(attr => {
                const label = attr.attribute?.name ? `${attr.attribute.name}: ${attr.value}` : attr.value;
                attrSelect.innerHTML += `<option value="${attr.id}">${label}</option>`;
            });
            attrSelect.disabled = false;
        }
    }

    function submitNewStock() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const productIdEl = document.getElementById('modal-product-id');
        const productId = productIdEl ? productIdEl.value : null;

        if (!productId) {
            alert('Product ID not found');
            return;
        }

        const attributeSelect = document.getElementById('new-stock-attribute');
        const attributeValueId = attributeSelect ? attributeSelect.value : null;

        const skuEl = document.getElementById('new-stock-sku');
        const sku = skuEl ? skuEl.value : null;

        const quantityEl = document.getElementById('new-stock-quantity');
        const quantity = quantityEl ? quantityEl.value : null;

        const priceEl = document.getElementById('new-stock-price');
        const price = priceEl ? priceEl.value : null;

        const dateEl = document.getElementById('new-stock-date');
        const date = dateEl ? dateEl.value : null;

        const noteEl = document.getElementById('new-stock-note');
        const note = noteEl ? noteEl.value : '';

        if (!sku || !quantity || !price || !date) {
            alert('Please fill in all required fields');
            return;
        }

        fetch('{{ route('products.stock.create') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                product_id: productId,
                color_id: null,
                attribute_value_id: attributeValueId,
                sku: sku,
                quantity: parseInt(quantity),
                price: parseFloat(price),
                date: date,
                note: note,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeStockModal();
                location.reload();
            } else {
                alert(data.message || 'Error adding new stock');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding new stock');
        });
    }

    // Close modal when clicking outside
    document.getElementById('stock-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStockModal();
        }
    });
</script>
@endsection
