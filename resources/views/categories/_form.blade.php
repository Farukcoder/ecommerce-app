{{-- Shared form partial used by create.blade.php and edit.blade.php --}}
{{-- Expected vars: $category (Category|null), $action (string), $method (POST|PUT) --}}

@push('styles')
<style>
.image-drop-zone {
    border: 2px dashed var(--border);
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--muted);
}
.image-drop-zone:hover {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary), transparent 94%);
}
.image-drop-zone svg {
    width: 32px;
    height: 32px;
    color: var(--muted-foreground);
    margin: 0 auto 0.5rem;
    display: block;
}
.image-preview {
    position: relative;
    width: 160px;
    height: 160px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
    background: var(--background);
}
.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>
@endpush

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="category-form">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="card" style="max-width:780px;">
        <div class="card-header">
            <h3 class="card-title">Category Details</h3>
        </div>
        <div class="card-body">

            {{-- Name --}}
            <div class="form-group">
                <label for="name" class="form-label">
                    Category Name <span style="color:var(--destructive);">*</span>
                </label>
                  <input type="text" id="name" name="name"
                      class="form-input @error('name') is-invalid @enderror"
                      value="{{ old('name', $category->name ?? '') }}"
                      placeholder="{{ __('messages.example_categories') }}" required maxlength="255"
                      oninput="autoSlug(this.value)">
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            {{-- Slug --}}
            <div class="form-group">
                <label for="slug" class="form-label">
                    Slug <span class="form-label-optional">(auto-generated if blank)</span>
                </label>
                <div style="position:relative;">
                          <input type="text" id="slug" name="slug"
                              class="form-input @error('slug') is-invalid @enderror"
                              value="{{ old('slug', $category->slug ?? '') }}"
                              placeholder="{{ __('messages.example_slug') }}" style="padding-left:2.75rem;" maxlength="255">
                    <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);color:var(--muted-foreground);font-size:0.875rem;">/</span>
                </div>
                @error('slug')<span class="form-error">{{ $message }}</span>@enderror
                <p class="form-hint">Used in URLs. Must be unique across categories.</p>
            </div>

            {{-- Image --}}
            <div class="form-group">
                <label class="form-label">Image <span class="form-label-optional">(optional)</span></label>

                @if(isset($category) && $category->image)
                <div id="existing-image" style="display:flex; align-items:flex-start; gap:1rem; margin-bottom:0.75rem;">
                    <div class="image-preview">
                        <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}">
                    </div>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.875rem; color:var(--muted-foreground); cursor:pointer;">
                        <input type="checkbox" name="remove_image" value="1" class="checkbox-input">
                        Remove current image
                    </label>
                </div>
                @endif

                <div class="image-drop-zone" onclick="document.getElementById('image-input').click()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p style="font-size:0.875rem; color:var(--muted-foreground); margin-bottom:0.25rem;">
                        {{ __('messages.click_drag_upload') }}
                    </p>
                    <p style="font-size:0.75rem; color:var(--muted-foreground);">PNG, JPG, WebP · Max 2MB</p>
                    <input type="file" id="image-input" name="image" accept="image/*" style="display:none;" onchange="previewImage(this)">
                </div>

                <div id="new-image-preview" style="display:none; margin-top:0.75rem;">
                    <div class="image-preview">
                        <img id="new-image-img" src="" alt="New image">
                    </div>
                </div>

                @error('image')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            {{-- Status --}}
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Status</label>
                <div style="display:flex; align-items:center; gap:0.625rem; padding-top:4px;">
                    <input type="hidden" name="status" value="0">
                    <label class="toggle-label" style="margin-bottom:0;">
                        <input type="checkbox" name="status" value="1" class="toggle-input" id="status-toggle"
                               {{ old('status', $category->status ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span style="font-size:0.875rem; color:var(--muted-foreground);" id="status-label" data-active="{{ __('messages.active') }}" data-inactive="{{ __('messages.inactive') }}">
                        {{ old('status', $category->status ?? true) ? __('messages.active') : __('messages.inactive') }}
                    </span>
                </div>
                <p class="form-hint">Inactive categories are hidden from product filters.</p>
            </div>

        </div>
        <div class="card-footer" style="display:flex; gap:0.625rem; justify-content:flex-end;">
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ $method === 'PUT' ? 'Update Category' : 'Create Category' }}
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
const slugInput = document.getElementById('slug');
let slugTouched = {{ old('slug', $category->slug ?? '') ? 'true' : 'false' }};

slugInput.addEventListener('input', () => { slugTouched = true; });

function autoSlug(name) {
    if (slugTouched) return;
    slugInput.value = name.toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

function previewImage(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('new-image-img').src = e.target.result;
        document.getElementById('new-image-preview').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}

const statusToggle = document.getElementById('status-toggle');
const statusLabel  = document.getElementById('status-label');
statusToggle.addEventListener('change', () => {
    statusLabel.textContent = statusToggle.checked ? statusLabel.dataset.active : statusLabel.dataset.inactive;
});
</script>
@endpush
