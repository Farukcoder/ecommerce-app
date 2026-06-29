@extends('tyro-dashboard::layouts.user')

@section('title', 'Bulk Upload Products')

@push('styles')
<style>
.import-upload-box {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 200px;
    border: 2px dashed var(--border);
    border-radius: 16px;
    background: var(--muted);
    color: var(--muted-foreground);
    text-align: center;
    padding: 2rem;
    transition: all 0.2s ease;
    cursor: pointer;
}
.import-upload-box:hover,
.import-upload-box.dragover {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary), transparent 92%);
}
.import-upload-box svg {
    width: 42px;
    height: 42px;
    margin-bottom: 1rem;
}
.import-upload-box input[type=file] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.import-grid {
    display: grid;
    grid-template-columns: 1.35fr 0.85fr;
    gap: 1.5rem;
}
@media (max-width: 980px) {
    .import-grid {
        grid-template-columns: 1fr;
    }
}
.aside-panel {
    display: grid;
    gap: 1rem;
}
.aside-card {
    padding: 1.25rem;
    border: 1px solid var(--border);
    border-radius: 16px;
    background: var(--surface);
}
.aside-card h3 {
    margin-bottom: 0.75rem;
    font-size: 1rem;
}
.aside-card ul {
    margin: 0;
    padding-left: 1.25rem;
    color: var(--muted-foreground);
    line-height: 1.8;
}
.column-list {
    display: grid;
    gap: 0.85rem;
    margin-top: 0.5rem;
}
.column-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    color: var(--muted-foreground);
    font-size: 0.95rem;
    line-height: 1.6;
}
.column-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 9999px;
    background: var(--surface);
    color: var(--primary);
    font-size: 0.8rem;
    flex-shrink: 0;
}
.upload-note {
    margin-top: 0.75rem;
    color: var(--muted-foreground);
    font-size: 0.95rem;
    line-height: 1.6;
}
.import-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 1.5rem;
}
</style>
@endpush

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
            <p class="page-description">Import products from a spreadsheet to update your catalog faster.</p>
        </div>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('products.import.sample') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-3-3m3 3l3-3M5 21h14" />
                </svg>
                Download Sample CSV
            </a>
        </div>
    </div>
</div>

@if(session('import_errors'))
    <div class="card" style="margin-bottom: 1.25rem; border-color: color-mix(in srgb, var(--destructive), transparent 80%);">
        <div class="card-body">
            <div style="display:flex; gap:0.75rem; align-items:flex-start;">
                <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:rgba(239,68,68,0.12); color:var(--destructive);">!</span>
                <div>
                    <h3 style="font-size:1rem; margin:0 0 0.75rem; color:var(--destructive);">Import Errors</h3>
                    <ul style="margin:0; padding-left:1.25rem; color:var(--destructive); line-height:1.75;">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="import-grid">
            <div>
                <div class="card" style="margin-bottom:1.5rem;">
                    <div class="card-header">
                        <h3 class="card-title">Upload your import file</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('products.import.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label class="form-label" for="file">Select file</label>
                                <div class="import-upload-box" id="upload-box">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-3-3m3 3l3-3M5 21h14" />
                                    </svg>
                                    <strong id="file-label">Click or drop a CSV, XLS, or XLSX file here</strong>
                                    <p class="upload-note">Supported formats: .csv, .xlsx, .xls. The first row should contain the header names listed below.</p>
                                    <input type="file" id="file" name="file" accept=".xlsx,.xls,.csv,text/csv" required>
                                </div>
                                @error('file')<span class="form-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="base_path">Image Base Path</label>
                                <input type="text" class="form-input @error('base_path') is-invalid @enderror" id="base_path" name="base_path" value="{{ old('base_path', storage_path('app/imports')) }}">
                                <p class="form-hint">Point this to a folder on your machine, such as E:\images. Relative paths like images/sample-thumb.jpg will be checked from that folder.</p>
                                @error('base_path')<span class="form-error">{{ $message }}</span>@enderror
                            </div>

                            <div class="import-actions">
                                <a href="{{ route('products.index') }}" class="btn btn-ghost">Cancel</a>
                                <button type="submit" class="btn btn-primary">Import Products</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <aside class="aside-panel">
                <div class="aside-card">
                    <h3>How it works</h3>
                    <ul>
                        <li>Upload one spreadsheet file containing all products.</li>
                        <li>Use the sample file to match required headers.</li>
                        <li>Relative image paths resolve from the base path.</li>
                        <li>Import will create or update product records.</li>
                    </ul>
                </div>

                <div class="aside-card">
                    <h3>Supported columns</h3>
                    <div class="column-list">
                        <div class="column-item"><span class="column-icon">•</span><strong>Required:</strong> name, base_price</div>
                        <div class="column-item"><span class="column-icon">•</span><strong>Optional:</strong> slug, sku, short_description</div>
                        <div class="column-item"><span class="column-icon">•</span>description, sale_price, discount_type, discount_value</div>
                        <div class="column-item"><span class="column-icon">•</span>status, featured, brand_id, category_ids</div>
                        <div class="column-item"><span class="column-icon">•</span>thumbnail_path, gallery_paths, stock_sku</div>
                        <div class="column-item"><span class="column-icon">•</span>stock_quantity, stock_price, meta_title</div>
                        <div class="column-item"><span class="column-icon">•</span>meta_description, attribute_id, attribute_name</div>
                        <div class="column-item"><span class="column-icon">•</span>attribute_value_id, attribute_value</div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const uploadBox = document.getElementById('upload-box');
    const fileInput = document.getElementById('file');
    const fileLabel = document.getElementById('file-label');

    fileInput.addEventListener('change', () => {
        fileLabel.textContent = fileInput.files.length ? fileInput.files[0].name : 'Click or drop a CSV, XLS, or XLSX file here';
    });

    uploadBox.addEventListener('dragover', (event) => {
        event.preventDefault();
        uploadBox.classList.add('dragover');
    });

    uploadBox.addEventListener('dragleave', () => {
        uploadBox.classList.remove('dragover');
    });

    uploadBox.addEventListener('drop', (event) => {
        event.preventDefault();
        uploadBox.classList.remove('dragover');

        const files = event.dataTransfer.files;
        if (!files.length) {
            return;
        }

        const data = new DataTransfer();
        Array.from(files).forEach((file) => data.items.add(file));
        fileInput.files = data.files;
        fileLabel.textContent = files[0].name;
    });
</script>
@endpush
