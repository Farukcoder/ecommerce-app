@php
    $fieldValue = function (string $key, $default = '') use ($systemSetting) {
        return old($key, $systemSetting->{$key} ?? $default);
    };
@endphp

<form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <div class="grid-2" style="align-items:start;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">General Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="system_name" class="form-label">System Name</label>
                    <input type="text" id="system_name" name="system_name" class="form-input @error('system_name') is-invalid @enderror" value="{{ $fieldValue('system_name', 'Active eCommerce CMS') }}" required>
                    @error('system_name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="frontend_website_name" class="form-label">Frontend Website Name</label>
                    <input type="text" id="frontend_website_name" name="frontend_website_name" class="form-input @error('frontend_website_name') is-invalid @enderror" value="{{ $fieldValue('frontend_website_name', 'Active eCommerce') }}" required>
                    @error('frontend_website_name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="site_motto" class="form-label">Site Motto</label>
                    <input type="text" id="site_motto" name="site_motto" class="form-input @error('site_motto') is-invalid @enderror" value="{{ $fieldValue('site_motto', 'Demo of Active eCommerce CMS') }}">
                    @error('site_motto')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="uploaded_image_format" class="form-label">Uploaded Image Format</label>
                    <select id="uploaded_image_format" name="uploaded_image_format" class="form-select @error('uploaded_image_format') is-invalid @enderror" required>
                        @foreach(['webp', 'jpg', 'jpeg', 'png', 'svg'] as $format)
                            <option value="{{ $format }}" {{ $fieldValue('uploaded_image_format', 'webp') === $format ? 'selected' : '' }}>{{ strtoupper($format) }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint">SVG and GIF files will not be converted.</p>
                    @error('uploaded_image_format')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="product_default_image">Product Default Image</label>
                    <input type="file" id="product_default_image" name="product_default_image" class="form-input @error('product_default_image') is-invalid @enderror" style="padding:0.5rem;" accept="image/*,.svg">
                    <p class="form-hint">Shown when a product has no thumbnail or gallery image.</p>
                    @error('product_default_image')<span class="form-error">{{ $message }}</span>@enderror

                    @if(!empty($systemSetting->product_default_image))
                        <div style="margin-top:0.75rem; display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                            <div style="border:1px solid var(--border); border-radius:10px; padding:0.5rem; background:var(--background);">
                                <img src="{{ $systemSetting->product_default_image_url }}" alt="Product Default Image" style="max-height: 84px; max-width: 260px; object-fit: contain;">
                            </div>
                            <span class="badge badge-secondary">Current image</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Brand Colors</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="website_base_color" class="form-label">Website Base Color</label>
                        <input type="text" id="website_base_color" name="website_base_color" class="form-input @error('website_base_color') is-invalid @enderror" value="{{ $fieldValue('website_base_color', '#0080FF') }}" required>
                        <p class="form-hint">Hex color code.</p>
                        @error('website_base_color')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="website_base_hover_color" class="form-label">Website Base Hover Color</label>
                        <input type="text" id="website_base_hover_color" name="website_base_hover_color" class="form-input @error('website_base_hover_color') is-invalid @enderror" value="{{ $fieldValue('website_base_hover_color', '#0066CC') }}" required>
                        <p class="form-hint">Hex color code.</p>
                        @error('website_base_hover_color')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="website_secondary_base_color" class="form-label">Website Secondary Base Color</label>
                        <input type="text" id="website_secondary_base_color" name="website_secondary_base_color" class="form-input @error('website_secondary_base_color') is-invalid @enderror" value="{{ $fieldValue('website_secondary_base_color', '#171717') }}" required>
                        <p class="form-hint">Hex color code.</p>
                        @error('website_secondary_base_color')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="website_secondary_base_hover_color" class="form-label">Website Secondary Base Hover Color</label>
                        <input type="text" id="website_secondary_base_hover_color" name="website_secondary_base_hover_color" class="form-input @error('website_secondary_base_hover_color') is-invalid @enderror" value="{{ $fieldValue('website_secondary_base_hover_color', '#5D5D62') }}" required>
                        <p class="form-hint">Hex color code.</p>
                        @error('website_secondary_base_hover_color')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid-2" style="margin-top: 1.5rem; align-items:start;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Identity Assets</h3>
            </div>
            <div class="card-body">
                @foreach([
                    'site_icon' => ['label' => 'Site Icon', 'hint' => 'Minimum dimensions required: 32px width x 32px height.'],
                    'system_logo_white' => ['label' => 'System Logo - White', 'hint' => 'Used in the admin panel side menu. Minimum dimensions required: 180px width x 31px height.'],
                    'system_logo_black' => ['label' => 'System Logo - Black', 'hint' => 'Used in the admin login page. Minimum dimensions required: 180px width x 31px height.'],
                ] as $field => $config)
                    <div class="form-group">
                        <label class="form-label" for="{{ $field }}">{{ $config['label'] }}</label>
                        <input type="file" id="{{ $field }}" name="{{ $field }}" class="form-input" style="padding:0.5rem;" accept="image/*,.svg">
                        <p class="form-hint">{{ $config['hint'] }}</p>
                        @error($field)<span class="form-error">{{ $message }}</span>@enderror

                        @if(!empty($systemSetting->{$field}))
                            <div style="margin-top:0.75rem; display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                                <div style="border:1px solid var(--border); border-radius:10px; padding:0.5rem; background:var(--background);">
                                    <img src="{{ $systemSetting->{$field . '_url'} }}" alt="{{ $config['label'] }}" style="max-height: 84px; max-width: 260px; object-fit: contain;">
                                </div>
                                <span class="badge badge-secondary">Current file</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Promotional Banners</h3>
            </div>
            <div class="card-body">
                @foreach([
                    'flash_deal_page_banner_large' => ['label' => 'Flash Deal Page Banner - Large', 'hint' => 'Will be shown in large devices. Minimum dimensions required: 1370px width x 242px height.'],
                    'flash_deal_page_banner_small' => ['label' => 'Flash Deal Page Banner - Small', 'hint' => 'Will be shown in small devices. Minimum dimensions required: 400px width x 148px height.'],
                ] as $field => $config)
                    <div class="form-group">
                        <label class="form-label" for="{{ $field }}">{{ $config['label'] }}</label>
                        <input type="file" id="{{ $field }}" name="{{ $field }}" class="form-input" style="padding:0.5rem;" accept="image/*,.svg">
                        <p class="form-hint">{{ $config['hint'] }}</p>
                        @error($field)<span class="form-error">{{ $message }}</span>@enderror

                        @if(!empty($systemSetting->{$field}))
                            <div style="margin-top:0.75rem; display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                                <div style="border:1px solid var(--border); border-radius:10px; padding:0.5rem; background:var(--background);">
                                    <img src="{{ $systemSetting->{$field . '_url'} }}" alt="{{ $config['label'] }}" style="max-width: 260px; width:100%; height:auto; object-fit: cover; border-radius: 6px;">
                                </div>
                                <span class="badge badge-secondary">Current banner</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div style="margin-top: 1.5rem; display:flex; justify-content:flex-end; gap:0.75rem; flex-wrap:wrap;">
        <a href="{{ route('system-settings.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
