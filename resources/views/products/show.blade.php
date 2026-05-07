@extends('tyro-dashboard::layouts.user')

@section('title', 'Product Details')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('products.index') }}">Products</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $product->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $product->name }}</h1>
            <p class="page-description">Product overview with pricing, stock and attributes.</p>
        </div>
        <div style="display:flex; gap:0.625rem; flex-wrap:wrap;">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back
            </a>
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
        </div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); margin-bottom:1rem;">
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Status</div>
            <div class="stat-value" style="font-size:1.125rem; text-transform:capitalize;">{{ $product->status }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Base Price</div>
            <div class="stat-value" style="font-size:1.125rem;">৳{{ number_format($product->base_price, 2) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Sale Price</div>
            <div class="stat-value" style="font-size:1.125rem;">{{ $product->sale_price ? '৳' . number_format($product->sale_price, 2) : '—' }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-content">
            <div class="stat-label">Total Stock</div>
            <div class="stat-value" style="font-size:1.125rem;">{{ $product->stocks->sum('quantity') }}</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h3 class="card-title">Product Media</h3></div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:0.75rem;">
            @if($product->thumbnail)
                <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;aspect-ratio:1;position:relative;">
                    <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
                    <span class="badge badge-primary" style="position:absolute;left:6px;bottom:6px;">Thumbnail</span>
                </div>
            @endif
            @foreach($product->images as $image)
                <div style="border:1px solid var(--border);border-radius:8px;overflow:hidden;aspect-ratio:1;position:relative;">
                    <img src="{{ asset('storage/' . $image->image) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
                    @if($image->is_primary)
                        <span class="badge badge-success" style="position:absolute;left:6px;bottom:6px;">Primary</span>
                    @endif
                </div>
            @endforeach
        </div>
        @if(!$product->thumbnail && $product->images->isEmpty())
            <p style="margin-top:0.625rem;color:var(--muted-foreground);font-size:0.875rem;">No images uploaded yet.</p>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h3 class="card-title">Product Details</h3></div>
    <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;">
        <div>
            <p><strong>SKU:</strong> {{ $product->sku ?: '—' }}</p>
            <p><strong>Brand:</strong> {{ $product->brand?->name ?: '—' }}</p>
            <p><strong>Featured:</strong> {{ $product->featured ? 'Yes' : 'No' }}</p>
            <p><strong>Discount:</strong> {{ $product->discount_type ? $product->discount_type . ' (' . $product->discount_value . ')' : '—' }}</p>
        </div>
        <div>
            <p><strong>Categories:</strong></p>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                @forelse($product->categories as $category)
                    <span class="badge badge-secondary">{{ $category->name }}</span>
                @empty
                    <span style="color:var(--muted-foreground);">—</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h3 class="card-title">Stock Variants</h3></div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Variant</th>
                    <th>SKU</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @forelse($product->stocks as $index => $stock)
                    <tr>
                        <td>
                            @if($stock->attributeValue && $stock->attributeValue->attribute)
                                {{ $stock->attributeValue->attribute->name }}: {{ $stock->attributeValue->value }}
                            @else
                                Variant {{ $index + 1 }}
                            @endif
                        </td>
                        <td>{{ $stock->sku }}</td>
                        <td>{{ $stock->quantity }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;color:var(--muted-foreground);">No stock rows found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h3 class="card-title">Attributes</h3></div>
    <div class="card-body">
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            @forelse($product->attributeValues as $attributeValue)
                <span class="badge badge-secondary">
                    {{ $attributeValue->attribute?->name }}: {{ $attributeValue->value }}
                </span>
            @empty
                <span style="color:var(--muted-foreground);">No attributes linked.</span>
            @endforelse
        </div>
    </div>
</div>

@if($product->description)
<div class="card">
    <div class="card-header"><h3 class="card-title">Description</h3></div>
    <div class="card-body" style="line-height:1.7;">
        {!! $product->description !!}
    </div>
</div>
@endif
@endsection
