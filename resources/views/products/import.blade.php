@extends('tyro-dashboard::layouts.user')

@section('title', 'Bulk Upload Products')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('products.index') }}">Products</a>
<span class="breadcrumb-separator">/</span>
<span>Bulk Upload</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Bulk Upload Products</h1>
            <p class="page-description">Upload an Excel or CSV file to import multiple products at once.</p>
        </div>
        <a href="{{ route('products.import.sample') }}" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-3-3m3 3l3-3M5 21h14" />
            </svg>
            Download Sample CSV
        </a>
    </div>
</div>

@if(session('import_errors'))
    <div class="card" style="margin-bottom: 1rem; border-color: color-mix(in srgb, var(--destructive), transparent 80%);">
        <div class="card-body">
            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--destructive);">Import Errors</h3>
            <ul style="margin: 0; padding-left: 1.25rem; color: var(--destructive);">
                @foreach(session('import_errors') as $error)
                    <li style="margin-bottom: 0.35rem;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('products.import.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label" for="file">Excel or CSV File</label>
                <input type="file" class="form-input @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                <p class="form-hint">First row must be the header row with the supported column names.</p>
                @error('file')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="base_path">Image Base Path</label>
                <input type="text" class="form-input @error('base_path') is-invalid @enderror" id="base_path" name="base_path" value="{{ old('base_path', storage_path('app/imports')) }}">
                <p class="form-hint">If image paths in the sheet are relative, they will be resolved from this folder.</p>
                @error('base_path')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="card" style="margin: 1rem 0;">
                <div class="card-header">
                    <h3 class="card-title" style="font-size: 1rem;">Supported Columns</h3>
                </div>
                <div class="card-body">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.5rem;">
                        <div><strong>Required:</strong> name, base_price</div>
                        <div><strong>Optional:</strong> slug, sku, short_description</div>
                        <div>description, sale_price, discount_type, discount_value</div>
                        <div>status (draft, published, archived), featured</div>
                        <div>brand_id, category_ids (comma-separated IDs)</div>
                        <div>meta_title, meta_description</div>
                        <div>thumbnail_path, gallery_paths (comma-separated)</div>
                        <div>stock_sku, stock_quantity, stock_price</div>
                        <div>attribute_id, attribute_value_id, attribute_name, attribute_value</div>
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem; flex-wrap:wrap;">
                <a href="{{ route('products.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Import Products</button>
            </div>
        </form>
    </div>
</div>
@endsection
