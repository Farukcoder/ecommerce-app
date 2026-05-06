@extends('tyro-dashboard::layouts.user')

@section('title', 'Add Product')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('products.index') }}">Products</a>
<span class="breadcrumb-separator">/</span>
<span>Add Product</span>
@endsection

@push('styles')
<style>
.product-layout { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start; }
@media (max-width: 1024px) { .product-layout { grid-template-columns: 1fr; } }
.drop-zone { border: 2px dashed var(--border); border-radius: 8px; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s; background: var(--muted); }
.drop-zone:hover, .drop-zone.dragover { border-color: var(--primary); background: color-mix(in srgb, var(--primary), transparent 94%); }
.drop-zone svg { width: 36px; height: 36px; color: var(--muted-foreground); margin: 0 auto 0.75rem; display: block; }
.image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(88px, 1fr)); gap: 0.625rem; margin-top: 1rem; }
.image-thumb { position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 1px solid var(--border); background: var(--muted); }
.image-thumb img { width:100%; height:100%; object-fit:cover; }
.image-thumb .remove-btn { position:absolute; top:4px; right:4px; background:rgba(0,0,0,0.6); border:none; border-radius:4px; color:#fff; width:22px; height:22px; cursor:pointer; display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.15s; }
.image-thumb:hover .remove-btn { opacity:1; }
.image-thumb .primary-badge { position:absolute; bottom:4px; left:4px; background:var(--primary); color:var(--primary-foreground); font-size:0.625rem; padding:2px 5px; border-radius:3px; font-weight:600; }
.attr-row { display:grid; grid-template-columns: 1fr 1fr auto; gap:0.75rem; align-items:start; margin-bottom:0.75rem; }
.attr-row:last-child { margin-bottom:0; }
.variant-badge { display:inline-flex; align-items:center; gap:4px; background:var(--muted); border:1px solid var(--border); border-radius:5px; padding:3px 8px; font-size:0.8125rem; }
.variant-badge .x { cursor:pointer; color:var(--muted-foreground); line-height:1; }
.sidebar-card { margin-bottom: 1rem; }
.sidebar-card:last-child { margin-bottom: 0; }
.toggle-row { display:flex; align-items:center; justify-content:space-between; padding: 0.625rem 0; }
.price-row { display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Product</h1>
            <p class="page-description">Fill in the details below to add a new product to your catalog.</p>
        </div>
        <div style="display:flex; gap:0.625rem; flex-wrap:wrap;">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back
            </a>
            <button type="button" class="btn btn-secondary" onclick="submitProductForm('draft')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Save Draft
            </button>
            <button type="button" class="btn btn-primary" onclick="submitProductForm('published')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Publish
            </button>
        </div>
    </div>
</div>

<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
@csrf
<input type="hidden" name="status" id="form-status" value="draft">

<div class="product-layout">

    {{-- ===== LEFT COLUMN ===== --}}
    <div>

        {{-- 1. Basic Information --}}
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;display:inline;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Basic Information
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="name" class="form-label">Product Name <span style="color:var(--destructive);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="e.g. Premium Wireless Headphones" required
                           oninput="generateSlug(this.value)">
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-row" style="grid-template-columns:1fr 1fr;">
                    <div class="form-group">
                        <label for="slug" class="form-label">Slug <span class="form-label-optional">(auto-generated)</span></label>
                        <div style="position:relative;">
                            <input type="text" id="slug" name="slug" class="form-input @error('slug') is-invalid @enderror"
                                   value="{{ old('slug') }}" placeholder="premium-wireless-headphones" style="padding-left:2.75rem;">
                            <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:var(--muted-foreground);font-size:0.875rem;">/</span>
                        </div>
                        @error('slug')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="sku" class="form-label">SKU <span class="form-label-optional">(optional)</span></label>
                        <input type="text" id="sku" name="sku" class="form-input @error('sku') is-invalid @enderror"
                               value="{{ old('sku') }}" placeholder="e.g. WH-1000XM4">
                        @error('sku')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="short_description" class="form-label">Short Description</label>
                    <textarea id="short_description" name="short_description" class="form-textarea @error('short_description') is-invalid @enderror"
                              rows="2" placeholder="Brief product summary shown in listings…" maxlength="500">{{ old('short_description') }}</textarea>
                    <div class="form-hint" id="short-desc-count">0 / 500 characters</div>
                    @error('short_description')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="description" class="form-label">Full Description</label>
                    <div style="border:1px solid var(--input); border-radius:8px; overflow:hidden;">
                        {{-- Toolbar --}}
                        <div style="display:flex; gap:2px; padding:0.5rem; background:var(--muted); border-bottom:1px solid var(--border); flex-wrap:wrap;">
                            @foreach([['B','bold','<strong>B</strong>'],['I','italic','<em>I</em>'],['U','underline','<u>U</u>'],['H2','formatBlock|H2','H2'],['UL','insertUnorderedList','≡'],['OL','insertOrderedList','#'],['Link','createLink','🔗'],['Undo','undo','↩'],['Redo','redo','↪']] as [$label, $cmd, $html])
                            <button type="button" onclick="execCmd('{{ $cmd }}')" title="{{ $label }}"
                                style="min-width:30px; height:28px; padding:0 6px; background:var(--background); border:1px solid var(--border); border-radius:5px; cursor:pointer; font-size:0.8125rem; color:var(--foreground); font-family:inherit; display:flex; align-items:center; justify-content:center;">
                                {!! $html !!}
                            </button>
                            @endforeach
                        </div>
                        <div id="description-editor" contenteditable="true"
                             style="min-height:160px; padding:0.875rem 1rem; font-size:0.9375rem; color:var(--foreground); line-height:1.7; outline:none; background:var(--background);"
                             placeholder="Enter detailed product description…"></div>
                    </div>
                    <textarea name="description" id="description" style="display:none;">{{ old('description') }}</textarea>
                    @error('description')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        {{-- 2. Product Images --}}
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;display:inline;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Product Images
                </h3>
                <span class="badge badge-secondary">Up to 10 images</span>
            </div>
            <div class="card-body">
                <div class="form-row" style="grid-template-columns:1fr 1fr; margin-bottom:1.25rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Thumbnail</label>
                        <div class="drop-zone" id="thumbnail-zone" onclick="document.getElementById('thumbnail-input').click()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p style="font-size:0.875rem; color:var(--muted-foreground); margin-bottom:0.25rem;">Click or drag to upload</p>
                            <p style="font-size:0.75rem; color:var(--muted-foreground);">PNG, JPG, WebP · Max 2MB</p>
                            <input type="file" id="thumbnail-input" name="thumbnail" accept="image/*" style="display:none;" onchange="previewThumbnail(this)">
                        </div>
                        <div id="thumbnail-preview" style="display:none; margin-top:0.75rem; position:relative; width:100%; aspect-ratio:1; border-radius:8px; overflow:hidden; border:1px solid var(--border);">
                            <img id="thumbnail-img" src="" alt="Thumbnail" style="width:100%;height:100%;object-fit:cover;">
                            <button type="button" onclick="clearThumbnail()" style="position:absolute;top:6px;right:6px; background:rgba(0,0,0,0.65); border:none; border-radius:5px; color:#fff; cursor:pointer; padding:4px 6px; font-size:0.75rem;">Remove</button>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Gallery Images</label>
                        <div class="drop-zone" id="gallery-zone" onclick="document.getElementById('gallery-input').click()"
                             ondragover="handleDragOver(event,'gallery-zone')" ondragleave="handleDragLeave('gallery-zone')" ondrop="handleDrop(event)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            <p style="font-size:0.875rem; color:var(--muted-foreground); margin-bottom:0.25rem;">Add gallery images</p>
                            <p style="font-size:0.75rem; color:var(--muted-foreground);">Drag & drop or click to browse</p>
                            <input type="file" id="gallery-input" name="gallery[]" accept="image/*" multiple style="display:none;" onchange="addGalleryImages(this)">
                        </div>
                    </div>
                </div>
                <div class="image-grid" id="gallery-grid"></div>
                <p style="font-size:0.8125rem; color:var(--muted-foreground); margin-top:0.5rem;" id="gallery-hint" style="display:none;">Click an image to set it as the primary image.</p>
            </div>
        </div>

        {{-- 3. Variants & Attributes --}}
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;display:inline;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Variants & Attributes
                </h3>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addAttributeRow()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Attribute
                </button>
            </div>
            <div class="card-body">
                <div id="attributes-container"></div>
                <div id="no-attrs" style="text-align:center; padding:1.5rem 0; color:var(--muted-foreground); font-size:0.9375rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px;margin:0 auto 0.5rem;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    No attributes added yet. Click <strong>Add Attribute</strong> to begin.
                </div>
                <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border); display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;" id="generate-section" style="display:none!important;">
                    <button type="button" class="btn btn-secondary" onclick="generateVariants()" id="gen-btn" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Generate Variants
                    </button>
                    <span style="font-size:0.8125rem; color:var(--muted-foreground);" id="variant-count-hint"></span>
                </div>
            </div>
        </div>

        {{-- 4. Stock Management --}}
        <div class="card" style="margin-bottom:1.25rem;" id="stock-card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;display:inline;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Stock Management
                </h3>
                <span class="badge badge-secondary" id="variant-total-badge">0 variants</span>
            </div>
            <div class="table-container">
                <table class="table" id="variants-table">
                    <thead>
                        <tr>
                            <th>Variant</th>
                            <th>SKU</th>
                            <th style="width:120px;">Price</th>
                            <th style="width:100px;">Qty</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="variants-tbody">
                        <tr id="no-variants-row">
                            <td colspan="6" style="text-align:center; color:var(--muted-foreground); padding:2rem;">
                                Generate variants from attributes above to manage stock.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- end left column --}}

    {{-- ===== RIGHT SIDEBAR ===== --}}
    <div>

        {{-- Publish Card --}}
        <div class="card sidebar-card">
            <div class="card-header">
                <h3 class="card-title">Publish</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="sidebar-status" class="form-label">Status</label>
                    <select name="_sidebar_status" id="sidebar-status" class="form-select" onchange="document.getElementById('form-status').value=this.value">
                        <option value="draft" {{ old('status','draft')==='draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status')==='published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ old('status')==='archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="toggle-row">
                    <div>
                        <div style="font-size:0.9375rem;font-weight:500;color:var(--foreground);">Featured Product</div>
                        <div style="font-size:0.8125rem;color:var(--muted-foreground);">Show in featured sections</div>
                    </div>
                    <label class="toggle-label" style="margin-bottom:0;">
                        <input type="checkbox" name="featured" value="1" class="toggle-input" id="featured-toggle" {{ old('featured') ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            <div class="card-footer" style="display:flex;gap:0.625rem;">
                <button type="button" class="btn btn-primary" style="flex:1;" onclick="submitProductForm('published')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Publish
                </button>
                <button type="button" class="btn btn-secondary" onclick="submitProductForm('draft')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Draft
                </button>
            </div>
        </div>

        {{-- Categories Card --}}
        <div class="card sidebar-card">
            <div class="card-header">
                <h3 class="card-title">Categories</h3>
            </div>
            <div class="card-body">
                <div class="form-group" style="margin-bottom:0.75rem;">
                    <input type="text" id="cat-search" class="form-input" placeholder="Search categories…" oninput="filterCategories(this.value)" style="font-size:0.875rem;">
                </div>
                <div id="categories-list" style="max-height:200px;overflow-y:auto;display:flex;flex-direction:column;gap:0.375rem;">
                    @forelse($categories as $cat)
                    <label style="display:flex;align-items:center;gap:0.625rem;cursor:pointer;padding:0.375rem 0.5rem;border-radius:6px;transition:background 0.1s;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background=''">
                        <input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="checkbox-input category-checkbox"
                               {{ in_array($cat->id, old('categories', [])) ? 'checked' : '' }}>
                        <span style="font-size:0.875rem;color:var(--foreground);">{{ $cat->name }}</span>
                    </label>
                    @empty
                    <p style="font-size:0.875rem;color:var(--muted-foreground);text-align:center;padding:1rem 0;">No categories found.</p>
                    @endforelse
                </div>
                @error('categories')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- Brand Card --}}
        <div class="card sidebar-card">
            <div class="card-header">
                <h3 class="card-title">Brand</h3>
            </div>
            <div class="card-body">
                <div class="form-group" style="margin-bottom:0;">
                    <select name="brand_id" id="brand_id" class="form-select">
                        <option value="">— Select Brand —</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        {{-- Pricing Card --}}
        <div class="card sidebar-card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:5px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pricing
                </h3>
            </div>
            <div class="card-body">
                <div class="price-row">
                    <div class="form-group">
                        <label for="base_price" class="form-label">Base Price <span style="color:var(--destructive);">*</span></label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:var(--muted-foreground);font-weight:500;">$</span>
                            <input type="number" id="base_price" name="base_price" class="form-input @error('base_price') is-invalid @enderror"
                                   value="{{ old('base_price') }}" placeholder="0.00" step="0.01" min="0" required style="padding-left:1.75rem;">
                        </div>
                        @error('base_price')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="sale_price" class="form-label">Sale Price</label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:var(--muted-foreground);font-weight:500;">$</span>
                            <input type="number" id="sale_price" name="sale_price" class="form-input @error('sale_price') is-invalid @enderror"
                                   value="{{ old('sale_price') }}" placeholder="0.00" step="0.01" min="0" style="padding-left:1.75rem;">
                        </div>
                        @error('sale_price')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="price-row" style="margin-top:0;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="discount_type" class="form-label">Discount Type</label>
                        <select id="discount_type" name="discount_type" class="form-select">
                            <option value="">None</option>
                            <option value="percentage" {{ old('discount_type')==='percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('discount_type')==='fixed' ? 'selected' : '' }}>Fixed ($)</option>
                        </select>
                        @error('discount_type')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="discount_value" class="form-label">Discount Value</label>
                        <input type="number" id="discount_value" name="discount_value" class="form-input @error('discount_value') is-invalid @enderror"
                               value="{{ old('discount_value') }}" placeholder="0" step="0.01" min="0">
                        @error('discount_value')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- SEO Card --}}
        <div class="card sidebar-card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:5px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    SEO
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" class="form-input"
                           value="{{ old('meta_title') }}" placeholder="SEO page title…" maxlength="255">
                    <div class="form-hint" id="meta-title-count">0 / 60 recommended</div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" class="form-textarea"
                              rows="3" placeholder="Brief description for search engines…" maxlength="500">{{ old('meta_description') }}</textarea>
                    <div class="form-hint" id="meta-desc-count">0 / 160 recommended</div>
                </div>
            </div>
        </div>

    </div>{{-- end sidebar --}}

</div>{{-- end product-layout --}}
</form>

@push('scripts')
<script>
// ── Attributes data from server ──────────────────────────────────────────────
const ATTRIBUTES = @json($attributes->map(fn($a) => ['id'=>$a->id,'name'=>$a->name,'values'=>$a->values->map(fn($v)=>['id'=>$v->id,'value'=>$v->value])]));

let attrRows    = [];
let galleryFiles = [];
let primaryIndex = 0;

// ── Status helper ────────────────────────────────────────────────────────────
function submitProductForm(status) {
    document.getElementById('form-status').value = status;
    document.getElementById('sidebar-status').value = status;
    document.getElementById('product-form').submit();
}

// ── Slug generator ───────────────────────────────────────────────────────────
function generateSlug(val) {
    document.getElementById('slug').value = val
        .toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g,'')
        .replace(/\s+/g,'-')
        .replace(/-+/g,'-');
}

// ── Rich text editor ─────────────────────────────────────────────────────────
function execCmd(cmd) {
    if (cmd.startsWith('formatBlock|')) {
        document.execCommand('formatBlock', false, cmd.split('|')[1]);
    } else if (cmd === 'createLink') {
        const url = prompt('Enter URL:');
        if (url) document.execCommand('createLink', false, url);
    } else {
        document.execCommand(cmd, false, null);
    }
    document.getElementById('description-editor').focus();
}
document.getElementById('description-editor').addEventListener('input', () => {
    document.getElementById('description').value = document.getElementById('description-editor').innerHTML;
});

// ── Character counters ───────────────────────────────────────────────────────
function charCounter(inputId, countId, limit) {
    const el = document.getElementById(inputId);
    const ct = document.getElementById(countId);
    if (!el || !ct) return;
    el.addEventListener('input', () => { ct.textContent = `${el.value.length} / ${limit}`; });
}
charCounter('short_description','short-desc-count', 500);
charCounter('meta_title','meta-title-count', 60);
charCounter('meta_description','meta-desc-count', 160);

// ── Thumbnail preview ────────────────────────────────────────────────────────
function previewThumbnail(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('thumbnail-img').src = e.target.result;
        document.getElementById('thumbnail-preview').style.display = 'block';
        document.getElementById('thumbnail-zone').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}
function clearThumbnail() {
    document.getElementById('thumbnail-input').value = '';
    document.getElementById('thumbnail-preview').style.display = 'none';
    document.getElementById('thumbnail-zone').style.display = 'block';
}

// ── Gallery images ───────────────────────────────────────────────────────────
function addGalleryImages(input) {
    [...input.files].forEach(file => {
        if (galleryFiles.length >= 10) return;
        galleryFiles.push(file);
        renderGallery();
    });
    input.value = '';
}
function renderGallery() {
    const grid = document.getElementById('gallery-grid');
    const hint = document.getElementById('gallery-hint');
    grid.innerHTML = '';
    galleryFiles.forEach((file, i) => {
        const url   = URL.createObjectURL(file);
        const thumb = document.createElement('div');
        thumb.className = 'image-thumb';
        thumb.title = 'Click to set as primary';
        thumb.onclick = () => { primaryIndex = i; renderGallery(); };
        thumb.innerHTML = `<img src="${url}">
            <button type="button" class="remove-btn" onclick="event.stopPropagation();removeGallery(${i})">✕</button>
            ${i===primaryIndex ? '<span class="primary-badge">Primary</span>' : ''}`;
        grid.appendChild(thumb);
    });
    if (hint) hint.style.display = galleryFiles.length ? 'block' : 'none';
}
function removeGallery(i) {
    galleryFiles.splice(i,1);
    if (primaryIndex >= galleryFiles.length) primaryIndex = 0;
    renderGallery();
}

// ── Drag & drop ──────────────────────────────────────────────────────────────
function handleDragOver(e, zoneId) { e.preventDefault(); document.getElementById(zoneId).classList.add('dragover'); }
function handleDragLeave(zoneId) { document.getElementById(zoneId).classList.remove('dragover'); }
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('gallery-zone').classList.remove('dragover');
    const dt = e.dataTransfer;
    if (dt.files) addGalleryImages({files: dt.files, value:''});
}

// ── Category search ──────────────────────────────────────────────────────────
function filterCategories(q) {
    document.querySelectorAll('#categories-list label').forEach(label => {
        const txt = label.querySelector('span').textContent.toLowerCase();
        label.style.display = txt.includes(q.toLowerCase()) ? '' : 'none';
    });
}

// ── Attributes & Variants ────────────────────────────────────────────────────
function addAttributeRow() {
    document.getElementById('no-attrs').style.display = 'none';
    document.getElementById('generate-section').style.removeProperty('display');
    const id = Date.now();
    attrRows.push({id, attrId: '', values: []});
    renderAttrRows();
}
function removeAttrRow(id) {
    attrRows = attrRows.filter(r => r.id !== id);
    if (!attrRows.length) {
        document.getElementById('no-attrs').style.display = '';
        document.getElementById('generate-section').style.display = 'none';
    }
    renderAttrRows();
    updateGenBtn();
}
function renderAttrRows() {
    const container = document.getElementById('attributes-container');
    container.innerHTML = '';
    attrRows.forEach(row => {
        const div = document.createElement('div');
        div.className = 'attr-row';
        div.dataset.id = row.id;
        const attrOpts = ATTRIBUTES.map(a =>
            `<option value="${a.id}" ${row.attrId==a.id?'selected':''}>${a.name}</option>`
        ).join('');
        const valueTagsHtml = row.values.map(v =>
            `<span class="variant-badge">${v}<span class="x" onclick="removeAttrValue(${row.id},'${v}')">✕</span></span>`
        ).join('');
        div.innerHTML = `
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="font-size:0.8125rem;">Attribute</label>
                <select class="form-select" onchange="setAttr(${row.id},this.value)">
                    <option value="">— Select —</option>${attrOpts}
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label" style="font-size:0.8125rem;">Values</label>
                <div style="display:flex;flex-wrap:wrap;gap:4px;min-height:38px;border:1px solid var(--input);border-radius:8px;padding:5px 8px;background:var(--background);" id="vals-${row.id}">
                    ${valueTagsHtml}
                    <input type="text" placeholder="Type & press Enter…" style="border:none;outline:none;font-size:0.875rem;color:var(--foreground);background:transparent;flex:1;min-width:80px;"
                        onkeydown="addAttrValue(event,${row.id})" id="val-input-${row.id}">
                </div>
            </div>
            <div style="padding-top:1.5rem;">
                <button type="button" class="action-btn action-btn-danger" title="Remove attribute" onclick="removeAttrRow(${row.id})">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>`;
        container.appendChild(div);
    });
    updateGenBtn();
}
function setAttr(rowId, attrId) {
    const row = attrRows.find(r => r.id === rowId);
    if (row) { row.attrId = attrId; row.values = []; renderAttrRows(); }
}
function addAttrValue(e, rowId) {
    if (e.key !== 'Enter' && e.key !== ',') return;
    e.preventDefault();
    const input = e.target;
    const val   = input.value.trim();
    if (!val) return;
    const row = attrRows.find(r => r.id === rowId);
    if (row && !row.values.includes(val)) { row.values.push(val); renderAttrRows(); }
    setTimeout(() => { const inp = document.getElementById(`val-input-${rowId}`); if(inp) inp.focus(); }, 0);
}
function removeAttrValue(rowId, val) {
    const row = attrRows.find(r => r.id === rowId);
    if (row) { row.values = row.values.filter(v => v !== val); renderAttrRows(); }
}
function updateGenBtn() {
    const btn = document.getElementById('gen-btn');
    const hint = document.getElementById('variant-count-hint');
    if (!btn) return;
    const ready = attrRows.length > 0 && attrRows.every(r => r.attrId && r.values.length);
    btn.disabled = !ready;
    if (ready) {
        const count = attrRows.reduce((acc, r) => acc * r.values.length, 1);
        hint.textContent = `Will generate ${count} variant${count!==1?'s':''}`;
    } else {
        hint.textContent = 'Select an attribute and add at least one value to generate variants.';
    }
}
function generateVariants() {
    const combos = attrRows.reduce((acc, row) => {
        if (!acc.length) return row.values.map(v => [{attr: ATTRIBUTES.find(a=>a.id==row.attrId)?.name||'', val: v}]);
        return acc.flatMap(combo => row.values.map(v => [...combo, {attr: ATTRIBUTES.find(a=>a.id==row.attrId)?.name||'', val: v}]));
    }, []);
    const tbody = document.getElementById('variants-tbody');
    const noRow = document.getElementById('no-variants-row');
    if (noRow) noRow.remove();
    tbody.innerHTML = '';
    combos.forEach((combo, i) => {
        const label = combo.map(c => `${c.attr}: ${c.val}`).join(' / ');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span style="font-size:0.875rem;font-weight:500;color:var(--foreground);">${label}</span></td>
            <td><input type="text" name="variants[${i}][sku]" class="form-input" style="font-size:0.8125rem;padding:0.375rem 0.625rem;" placeholder="SKU-${i+1}"></td>
            <td><div style="position:relative;"><span style="position:absolute;left:0.625rem;top:50%;transform:translateY(-50%);color:var(--muted-foreground);font-size:0.8125rem;">$</span>
                <input type="number" name="variants[${i}][price]" class="form-input" style="font-size:0.8125rem;padding:0.375rem 0.625rem 0.375rem 1.375rem;" placeholder="0.00" step="0.01" min="0"></div></td>
            <td><input type="number" name="variants[${i}][quantity]" class="form-input" style="font-size:0.8125rem;padding:0.375rem 0.625rem;" placeholder="0" min="0" value="0"></td>
            <td><select name="variants[${i}][status]" class="form-select" style="font-size:0.8125rem;padding:0.375rem 1.5rem 0.375rem 0.625rem;">
                <option value="in_stock">In Stock</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="backorder">Backorder</option>
            </select></td>
            <td><button type="button" class="action-btn action-btn-danger" title="Remove" onclick="this.closest('tr').remove();updateVariantBadge();">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button></td>`;
        tbody.appendChild(tr);
    });
    updateVariantBadge();
}
function updateVariantBadge() {
    const count = document.querySelectorAll('#variants-tbody tr:not(#no-variants-row)').length;
    const badge = document.getElementById('variant-total-badge');
    if (badge) badge.textContent = `${count} variant${count!==1?'s':''}`;
}
</script>
@endpush
@endsection
