@php
    $fieldValue = function (string $key, $default = '') use ($systemSetting) {
        return old($key, $systemSetting->{$key} ?? $default);
    };

    $aboutValues = old('about_values', $systemSetting->about_values ?? []);
    if (!is_array($aboutValues) || empty($aboutValues)) {
        $aboutValues = [
            ['title' => 'Quality First', 'description' => ''],
        ];
    }

    $aboutTeamMembers = old('about_team_members', $systemSetting->about_team_members ?? []);
    if (!is_array($aboutTeamMembers) || empty($aboutTeamMembers)) {
        $aboutTeamMembers = [
            ['name' => '', 'role' => '', 'image' => null],
        ];
    }

    $contactInformation = old('contact_information', $systemSetting->contact_information ?? []);
    if (!is_array($contactInformation) || empty($contactInformation)) {
        $contactInformation = [
            ['icon' => 'email', 'title' => 'Email', 'details' => []],
        ];
    }

    $contactIconOptions = [
        'email' => 'Email',
        'phone' => 'Phone',
        'map-pin' => 'Address',
        'clock' => 'Business Hours',
    ];

    $availableCurrencies = old('available_currencies', $systemSetting->available_currencies ?? []);
    if (!is_array($availableCurrencies) || empty($availableCurrencies)) {
        $availableCurrencies = [
            ['code' => 'BDT', 'symbol' => '৳', 'exchange_rate' => 1, 'is_default' => true],
        ];
    }

    $currencyPresets = \App\Support\CurrencyFormatter::PRESET_CURRENCIES;
    $currencyPresetOptions = collect($currencyPresets)->map(fn ($preset, $code) => [
        'code' => $code,
        'symbol' => $preset['symbol'],
        'name' => $preset['name']
    ])->values();

    $availableLocales = old('available_locales', $systemSetting->available_locales ?? []);
    if (!is_array($availableLocales) || empty($availableLocales)) {
        $availableLocales = [
            ['code' => 'en', 'name' => 'English', 'is_default' => true],
            ['code' => 'bn', 'name' => 'Bangla', 'is_default' => false],
        ];
    }

    $localePresets = [
        'en' => ['code' => 'en', 'name' => 'English'],
        'bn' => ['code' => 'bn', 'name' => 'Bangla'],
        'ar' => ['code' => 'ar', 'name' => 'Arabic'],
        'es' => ['code' => 'es', 'name' => 'Spanish'],
        'fr' => ['code' => 'fr', 'name' => 'French'],
        'hi' => ['code' => 'hi', 'name' => 'Hindi'],
    ];
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
            <div class="card-header">
                <h3 class="card-title">Website Hero Section</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="hero_badge_text" class="form-label">Homepage Hero Badge</label>
                    <input type="text" id="hero_badge_text" name="hero_badge_text" class="form-input @error('hero_badge_text') is-invalid @enderror" value="{{ $fieldValue('hero_badge_text', 'New Season Collection') }}">
                    @error('hero_badge_text')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="hero_heading" class="form-label">Homepage Hero Heading</label>
                    <input type="text" id="hero_heading" name="hero_heading" class="form-input @error('hero_heading') is-invalid @enderror" value="{{ $fieldValue('hero_heading', 'Discover Your Perfect Style') }}">
                    @error('hero_heading')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="hero_description" class="form-label">Homepage Hero Description</label>
                    <textarea id="hero_description" name="hero_description" rows="3" class="form-input @error('hero_description') is-invalid @enderror">{{ $fieldValue('hero_description', 'Curated collections of premium products designed for the modern lifestyle. Quality meets elegance.') }}</textarea>
                    @error('hero_description')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
        
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Currency Settings</h3>
        </div>
        <div class="card-body">
            <p class="form-hint" style="margin-bottom: 1rem;">Configure the default store currency and optional additional currencies for the storefront switcher. Exchange rates are relative to the default currency (rate = 1 for default).</p>

            <div class="form-row">
                <div class="form-group">
                    <label for="currency_code" class="form-label">Default Currency Code</label>
                    <select id="currency_code" name="currency_code" class="form-select @error('currency_code') is-invalid @enderror" required>
                        @foreach($currencyPresets as $code => $preset)
                            <option value="{{ $code }}" data-symbol="{{ $preset['symbol'] }}" {{ $fieldValue('currency_code', 'BDT') === $code ? 'selected' : '' }}>{{ $code }} — {{ $preset['name'] }}</option>
                        @endforeach
                        @if(!array_key_exists($fieldValue('currency_code', 'BDT'), $currencyPresets))
                            <option value="{{ $fieldValue('currency_code') }}" selected>{{ $fieldValue('currency_code') }}</option>
                        @endif
                    </select>
                    @error('currency_code')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="currency_symbol" class="form-label">Currency Symbol</label>
                    <input type="text" id="currency_symbol" name="currency_symbol" class="form-input @error('currency_symbol') is-invalid @enderror" value="{{ $fieldValue('currency_symbol', '৳') }}" required>
                    @error('currency_symbol')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="currency_symbol_position" class="form-label">Symbol Position</label>
                    <select id="currency_symbol_position" name="currency_symbol_position" class="form-select @error('currency_symbol_position') is-invalid @enderror" required>
                        <option value="before" {{ $fieldValue('currency_symbol_position', 'before') === 'before' ? 'selected' : '' }}>Before amount (e.g. ৳100)</option>
                        <option value="before_with_space" {{ $fieldValue('currency_symbol_position') === 'before_with_space' ? 'selected' : '' }}>Before with space (e.g. BDT 100)</option>
                        <option value="after" {{ $fieldValue('currency_symbol_position') === 'after' ? 'selected' : '' }}>After amount (e.g. 100৳)</option>
                    </select>
                    @error('currency_symbol_position')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="currency_decimal_places" class="form-label">Decimal Places</label>
                    <input type="number" id="currency_decimal_places" name="currency_decimal_places" class="form-input @error('currency_decimal_places') is-invalid @enderror" value="{{ $fieldValue('currency_decimal_places', 2) }}" min="0" max="4" required>
                    @error('currency_decimal_places')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="currency_thousands_separator" class="form-label">Thousands Separator</label>
                    <input type="text" id="currency_thousands_separator" name="currency_thousands_separator" class="form-input @error('currency_thousands_separator') is-invalid @enderror" value="{{ $fieldValue('currency_thousands_separator', ',') }}" required>
                    @error('currency_thousands_separator')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="currency_decimal_separator" class="form-label">Decimal Separator</label>
                    <input type="text" id="currency_decimal_separator" name="currency_decimal_separator" class="form-input @error('currency_decimal_separator') is-invalid @enderror" value="{{ $fieldValue('currency_decimal_separator', '.') }}" required>
                    @error('currency_decimal_separator')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <h4 style="font-size: 0.9375rem; font-weight: 600; margin-bottom: 0.75rem;">Available Currencies (for storefront switcher)</h4>
                <div id="available-currency-rows" style="display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach($availableCurrencies as $index => $currency)
                        <div class="available-currency-row" style="display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:0.75rem; align-items:center;">
                            <select name="available_currencies[{{ $index }}][code]" class="form-select currency-code-select">
                                @foreach($currencyPresets as $code => $preset)
                                    <option value="{{ $code }}" data-symbol="{{ $preset['symbol'] }}" {{ ($currency['code'] ?? '') === $code ? 'selected' : '' }}>{{ $code }}</option>
                                @endforeach
                                @if(!empty($currency['code']) && !array_key_exists($currency['code'], $currencyPresets))
                                    <option value="{{ $currency['code'] }}" selected>{{ $currency['code'] }}</option>
                                @endif
                            </select>
                            <input type="text" name="available_currencies[{{ $index }}][symbol]" class="form-input currency-symbol-input" placeholder="Symbol" value="{{ $currency['symbol'] ?? '' }}">
                            <input type="number" name="available_currencies[{{ $index }}][exchange_rate]" class="form-input" placeholder="Exchange rate" step="0.000001" min="0" value="{{ $currency['exchange_rate'] ?? 1 }}">
                            <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-currency-row aria-label="Remove currency">×</button>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top:0.75rem;">
                    <button type="button" class="btn btn-secondary btn-sm" id="add-currency-row">Add Currency</button>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Language Settings</h3>
            </div>
            <div class="card-body">
                <p class="form-hint" style="margin-bottom: 1rem;">Configure the default store language and active languages. You can select English (en) and Bangla (bn) for now.</p>

                <div class="form-row">
                    <div class="form-group">
                        <label for="default_locale" class="form-label">Default Language</label>
                        <select id="default_locale" name="default_locale" class="form-select @error('default_locale') is-invalid @enderror" required>
                            @foreach($localePresets as $code => $preset)
                                <option value="{{ $code }}" {{ $fieldValue('default_locale', 'en') === $code ? 'selected' : '' }}>{{ $preset['name'] }} ({{ $code }})</option>
                            @endforeach
                        </select>
                        @error('default_locale')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <h4 style="font-size: 0.9375rem; font-weight: 600; margin-bottom: 0.75rem;">Available Languages</h4>
                    <div id="available-locale-rows" style="display:flex; flex-direction:column; gap:0.75rem;">
                        @foreach($availableLocales as $index => $locale)
                            <div class="available-locale-row" style="display:grid; grid-template-columns: 1fr 2fr auto; gap:0.75rem; align-items:center;">
                                <select name="available_locales[{{ $index }}][code]" class="form-select locale-code-select">
                                    @foreach($localePresets as $code => $preset)
                                        <option value="{{ $code }}" data-name="{{ $preset['name'] }}" {{ ($locale['code'] ?? '') === $code ? 'selected' : '' }}>{{ $code }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="available_locales[{{ $index }}][name]" class="form-input locale-name-input" placeholder="Name" value="{{ $locale['name'] ?? '' }}">
                                <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-locale-row aria-label="Remove language">×</button>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top:0.75rem;">
                        <button type="button" class="btn btn-secondary btn-sm" id="add-locale-row">Add Language</button>
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
                    'flash_deal_page_banner_large' => ['label' => 'Flash Deal / About Page Banner - Large', 'hint' => 'Used on the Flash Deal page and About page hero. Minimum dimensions required: 1370px width x 242px height.'],
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

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">About Page</h3>
        </div>
        <div class="card-body">
            <p class="form-hint" style="margin-bottom: 1rem;">The About page hero image uses the <strong>Flash Deal / About Page Banner - Large</strong> uploaded above.</p>

            <div class="form-row">
                <div class="form-group">
                    <label for="about_hero_heading" class="form-label">About Hero Heading</label>
                    <input type="text" id="about_hero_heading" name="about_hero_heading" class="form-input @error('about_hero_heading') is-invalid @enderror" value="{{ $fieldValue('about_hero_heading', 'Our Story') }}">
                    @error('about_hero_heading')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="about_hero_description" class="form-label">About Hero Description</label>
                <textarea id="about_hero_description" name="about_hero_description" rows="3" class="form-input @error('about_hero_description') is-invalid @enderror">{{ $fieldValue('about_hero_description', 'Founded in 2020, Nityodinar Kutir was born from a passion for quality craftsmanship and timeless design. We believe that exceptional products should be accessible to everyone.') }}</textarea>
                @error('about_hero_description')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div style="border-top: 1px solid var(--border); margin: 1.25rem 0; padding-top: 1.25rem;">
                <h4 style="font-size: 0.9375rem; margin-bottom: 1rem;">Our Mission</h4>

                <div class="form-group">
                    <label for="about_mission_heading" class="form-label">Mission Heading</label>
                    <input type="text" id="about_mission_heading" name="about_mission_heading" class="form-input @error('about_mission_heading') is-invalid @enderror" value="{{ $fieldValue('about_mission_heading', 'Our Mission') }}">
                    @error('about_mission_heading')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="about_mission_description" class="form-label">Mission Description</label>
                    <textarea id="about_mission_description" name="about_mission_description" rows="5" class="form-input @error('about_mission_description') is-invalid @enderror">{{ $fieldValue('about_mission_description') }}</textarea>
                    @error('about_mission_description')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="about_mission_button_text" class="form-label">Mission Button Text</label>
                        <input type="text" id="about_mission_button_text" name="about_mission_button_text" class="form-input @error('about_mission_button_text') is-invalid @enderror" value="{{ $fieldValue('about_mission_button_text', 'Explore Our Collection') }}">
                        @error('about_mission_button_text')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="about_mission_button_url" class="form-label">Mission Button URL</label>
                        <input type="text" id="about_mission_button_url" name="about_mission_button_url" class="form-input @error('about_mission_button_url') is-invalid @enderror" value="{{ $fieldValue('about_mission_button_url', '/shop') }}">
                        @error('about_mission_button_url')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="about_mission_image">Mission Image</label>
                    <input type="file" id="about_mission_image" name="about_mission_image" class="form-input @error('about_mission_image') is-invalid @enderror" style="padding:0.5rem;" accept="image/*,.svg">
                    @error('about_mission_image')<span class="form-error">{{ $message }}</span>@enderror

                    @if(!empty($systemSetting->about_mission_image))
                        <div style="margin-top:0.75rem; display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                            <div style="border:1px solid var(--border); border-radius:10px; padding:0.5rem; background:var(--background);">
                                <img src="{{ $systemSetting->about_mission_image_url }}" alt="Mission Image" style="max-height: 120px; max-width: 200px; object-fit: cover; border-radius: 6px;">
                            </div>
                            <span class="badge badge-secondary">Current image</span>
                        </div>
                    @endif
                </div>
            </div>

            <div style="border-top: 1px solid var(--border); margin: 1.25rem 0; padding-top: 1.25rem;">
                <h4 style="font-size: 0.9375rem; margin-bottom: 0.5rem;">Our Values</h4>
                <p class="form-hint" style="margin-bottom: 1rem;">Add value cards shown on the About page.</p>

                <div class="form-row" style="margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="about_values_heading" class="form-label">Values Section Heading</label>
                        <input type="text" id="about_values_heading" name="about_values_heading" class="form-input" value="{{ $fieldValue('about_values_heading', 'Our Values') }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="about_values_subheading" class="form-label">Values Section Subheading</label>
                        <input type="text" id="about_values_subheading" name="about_values_subheading" class="form-input" value="{{ $fieldValue('about_values_subheading', 'The principles that guide everything we do') }}">
                    </div>
                </div>

                <div id="about-values-rows" style="display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach($aboutValues as $index => $value)
                        <div class="about-value-row" style="display:grid; grid-template-columns: 1fr 2fr auto; gap:0.75rem; align-items:start;">
                            <input type="text" name="about_values[{{ $index }}][title]" class="form-input" placeholder="Title" value="{{ $value['title'] ?? '' }}">
                            <textarea name="about_values[{{ $index }}][description]" class="form-input" rows="2" placeholder="Description">{{ $value['description'] ?? '' }}</textarea>
                            <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-about-value-row aria-label="Remove value">×</button>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top:0.75rem;">
                    <button type="button" class="btn btn-secondary btn-sm" id="add-about-value-row">Add One More</button>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border); margin: 1.25rem 0; padding-top: 1.25rem;">
                <h4 style="font-size: 0.9375rem; margin-bottom: 0.5rem;">Meet Our Team</h4>
                <p class="form-hint" style="margin-bottom: 1rem;">Add team member profiles shown on the About page.</p>

                <div class="form-row" style="margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="about_team_heading" class="form-label">Team Section Heading</label>
                        <input type="text" id="about_team_heading" name="about_team_heading" class="form-input" value="{{ $fieldValue('about_team_heading', 'Meet Our Team') }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="about_team_subheading" class="form-label">Team Section Subheading</label>
                        <input type="text" id="about_team_subheading" name="about_team_subheading" class="form-input" value="{{ $fieldValue('about_team_subheading', 'The passionate people behind Nityodinar Kutir') }}">
                    </div>
                </div>

                <div id="about-team-rows" style="display:flex; flex-direction:column; gap:1rem;">
                    @foreach($aboutTeamMembers as $index => $member)
                        @php $memberImage = $member['existing_image'] ?? $member['image'] ?? null; @endphp
                        <div class="about-team-row card" style="padding: 1rem;">
                            @if(!empty($memberImage))
                                <input type="hidden" name="about_team_members[{{ $index }}][existing_image]" value="{{ $memberImage }}">
                            @endif
                            <div style="display:grid; grid-template-columns: 1fr 1fr auto; gap:0.75rem; align-items:start;">
                                <input type="text" name="about_team_members[{{ $index }}][name]" class="form-input" placeholder="Name" value="{{ $member['name'] ?? '' }}">
                                <input type="text" name="about_team_members[{{ $index }}][role]" class="form-input" placeholder="Role" value="{{ $member['role'] ?? '' }}">
                                <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-about-team-row aria-label="Remove team member">×</button>
                            </div>
                            <div class="form-group" style="margin-top: 0.75rem; margin-bottom: 0;">
                                <label class="form-label" for="about_team_members_{{ $index }}_image">Photo</label>
                                <input type="file" id="about_team_members_{{ $index }}_image" name="about_team_members[{{ $index }}][image]" class="form-input" style="padding:0.5rem;" accept="image/*,.svg">
                                @if(!empty($memberImage))
                                    <div style="margin-top:0.5rem; display:flex; align-items:center; gap:0.75rem;">
                                        <img src="{{ asset('storage/' . $memberImage) }}" alt="{{ $member['name'] ?? 'Team member' }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                        <span class="badge badge-secondary">Current photo</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top:0.75rem;">
                    <button type="button" class="btn btn-secondary btn-sm" id="add-about-team-row">Add One More</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Contact Information</h3>
        </div>
        <div class="card-body">
            <p class="form-hint" style="margin-bottom: 1rem;">Contact details shown on the storefront contact page. Enter each detail on a new line.</p>

            <div id="contact-information-rows" style="display:flex; flex-direction:column; gap:1rem;">
                @foreach($contactInformation as $index => $item)
                    @php
                        $detailsText = is_array($item['details'] ?? null)
                            ? implode("\n", $item['details'])
                            : (string) ($item['details'] ?? '');
                    @endphp
                    <div class="contact-information-row card" style="padding: 1rem;">
                        <div style="display:grid; grid-template-columns: 160px 1fr auto; gap:0.75rem; align-items:start;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="contact_information_{{ $index }}_icon">Icon</label>
                                <select id="contact_information_{{ $index }}_icon" name="contact_information[{{ $index }}][icon]" class="form-select">
                                    @foreach($contactIconOptions as $iconValue => $iconLabel)
                                        <option value="{{ $iconValue }}" {{ ($item['icon'] ?? 'email') === $iconValue ? 'selected' : '' }}>{{ $iconLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="contact_information_{{ $index }}_title">Title</label>
                                <input type="text" id="contact_information_{{ $index }}_title" name="contact_information[{{ $index }}][title]" class="form-input" placeholder="Title" value="{{ $item['title'] ?? '' }}">
                            </div>
                            <button type="button" class="btn btn-ghost" style="color: var(--danger); margin-top: 1.75rem;" data-remove-contact-information-row aria-label="Remove contact item">×</button>
                        </div>
                        <div class="form-group" style="margin-top: 0.75rem; margin-bottom: 0;">
                            <label class="form-label" for="contact_information_{{ $index }}_details">Details</label>
                            <textarea id="contact_information_{{ $index }}_details" name="contact_information[{{ $index }}][details]" class="form-input" rows="3" placeholder="One detail per line">{{ $detailsText }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:0.75rem;">
                <button type="button" class="btn btn-secondary btn-sm" id="add-contact-information-row">Add One More</button>
            </div>
        </div>
    </div>

    <div style="margin-top: 1.5rem; display:flex; justify-content:flex-end; gap:0.75rem; flex-wrap:wrap;">
        <a href="{{ route('system-settings.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
<script>
(() => {
    const valuesRows = document.getElementById('about-values-rows');
    const addValueButton = document.getElementById('add-about-value-row');
    let nextValueIndex = valuesRows ? valuesRows.querySelectorAll('.about-value-row').length : 0;

    if (valuesRows && addValueButton) {
        const createValueRow = (title = '', description = '') => {
            const index = nextValueIndex++;
            const row = document.createElement('div');
            row.className = 'about-value-row';
            row.style.display = 'grid';
            row.style.gridTemplateColumns = '1fr 2fr auto';
            row.style.gap = '0.75rem';
            row.style.alignItems = 'start';
            row.innerHTML = `
                <input type="text" name="about_values[${index}][title]" class="form-input" placeholder="Title" value="${title.replace(/"/g, '&quot;')}">
                <textarea name="about_values[${index}][description]" class="form-input" rows="2" placeholder="Description">${description.replace(/</g, '&lt;')}</textarea>
                <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-about-value-row aria-label="Remove value">×</button>
            `;
            return row;
        };

        addValueButton.addEventListener('click', () => {
            valuesRows.appendChild(createValueRow());
        });

        valuesRows.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-about-value-row]');
            if (!button) {
                return;
            }

            const row = button.closest('.about-value-row');
            if (row && valuesRows.querySelectorAll('.about-value-row').length > 1) {
                row.remove();
            }
        });
    }

    const teamRows = document.getElementById('about-team-rows');
    const addTeamButton = document.getElementById('add-about-team-row');
    let nextTeamIndex = teamRows ? teamRows.querySelectorAll('.about-team-row').length : 0;

    if (teamRows && addTeamButton) {
        const createTeamRow = (name = '', role = '') => {
            const index = nextTeamIndex++;
            const row = document.createElement('div');
            row.className = 'about-team-row card';
            row.style.padding = '1rem';
            row.innerHTML = `
                <div style="display:grid; grid-template-columns: 1fr 1fr auto; gap:0.75rem; align-items:start;">
                    <input type="text" name="about_team_members[${index}][name]" class="form-input" placeholder="Name" value="${name.replace(/"/g, '&quot;')}">
                    <input type="text" name="about_team_members[${index}][role]" class="form-input" placeholder="Role" value="${role.replace(/"/g, '&quot;')}">
                    <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-about-team-row aria-label="Remove team member">×</button>
                </div>
                <div class="form-group" style="margin-top: 0.75rem; margin-bottom: 0;">
                    <label class="form-label" for="about_team_members_${index}_image">Photo</label>
                    <input type="file" id="about_team_members_${index}_image" name="about_team_members[${index}][image]" class="form-input" style="padding:0.5rem;" accept="image/*,.svg">
                </div>
            `;
            return row;
        };

        addTeamButton.addEventListener('click', () => {
            teamRows.appendChild(createTeamRow());
        });

        teamRows.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-about-team-row]');
            if (!button) {
                return;
            }

            const row = button.closest('.about-team-row');
            if (row && teamRows.querySelectorAll('.about-team-row').length > 1) {
                row.remove();
            }
        });
    }

    const contactRows = document.getElementById('contact-information-rows');
    const addContactButton = document.getElementById('add-contact-information-row');
    let nextContactIndex = contactRows ? contactRows.querySelectorAll('.contact-information-row').length : 0;

    if (contactRows && addContactButton) {
        const createContactRow = (icon = 'email', title = '', details = '') => {
            const index = nextContactIndex++;
            const row = document.createElement('div');
            row.className = 'contact-information-row card';
            row.style.padding = '1rem';
            row.innerHTML = `
                <div style="display:grid; grid-template-columns: 160px 1fr auto; gap:0.75rem; align-items:start;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="contact_information_${index}_icon">Icon</label>
                        <select id="contact_information_${index}_icon" name="contact_information[${index}][icon]" class="form-select">
                            <option value="email"${icon === 'email' ? ' selected' : ''}>Email</option>
                            <option value="phone"${icon === 'phone' ? ' selected' : ''}>Phone</option>
                            <option value="map-pin"${icon === 'map-pin' ? ' selected' : ''}>Address</option>
                            <option value="clock"${icon === 'clock' ? ' selected' : ''}>Business Hours</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="contact_information_${index}_title">Title</label>
                        <input type="text" id="contact_information_${index}_title" name="contact_information[${index}][title]" class="form-input" placeholder="Title" value="${title.replace(/"/g, '&quot;')}">
                    </div>
                    <button type="button" class="btn btn-ghost" style="color: var(--danger); margin-top: 1.75rem;" data-remove-contact-information-row aria-label="Remove contact item">×</button>
                </div>
                <div class="form-group" style="margin-top: 0.75rem; margin-bottom: 0;">
                    <label class="form-label" for="contact_information_${index}_details">Details</label>
                    <textarea id="contact_information_${index}_details" name="contact_information[${index}][details]" class="form-input" rows="3" placeholder="One detail per line">${details.replace(/</g, '&lt;')}</textarea>
                </div>
            `;
            return row;
        };

        addContactButton.addEventListener('click', () => {
            contactRows.appendChild(createContactRow());
        });

        contactRows.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-contact-information-row]');
            if (!button) {
                return;
            }

            const row = button.closest('.contact-information-row');
            if (row && contactRows.querySelectorAll('.contact-information-row').length > 1) {
                row.remove();
            }
        });
    }

    const currencyCodeSelect = document.getElementById('currency_code');
    const currencySymbolInput = document.getElementById('currency_symbol');

    if (currencyCodeSelect && currencySymbolInput) {
        currencyCodeSelect.addEventListener('change', () => {
            const option = currencyCodeSelect.selectedOptions[0];
            if (option?.dataset.symbol) {
                currencySymbolInput.value = option.dataset.symbol;
            }
        });
    }

    const currencyRows = document.getElementById('available-currency-rows');
    const addCurrencyButton = document.getElementById('add-currency-row');
    let nextCurrencyIndex = currencyRows ? currencyRows.querySelectorAll('.available-currency-row').length : 0;

    const currencyPresetOptions = @json($currencyPresetOptions);

    if (currencyRows && addCurrencyButton) {
        const bindCurrencyRow = (row) => {
            const select = row.querySelector('.currency-code-select');
            const symbolInput = row.querySelector('.currency-symbol-input');
            if (!select || !symbolInput) {
                return;
            }

            select.addEventListener('change', () => {
                const option = select.selectedOptions[0];
                if (option?.dataset.symbol) {
                    symbolInput.value = option.dataset.symbol;
                }
            });
        };

        currencyRows.querySelectorAll('.available-currency-row').forEach(bindCurrencyRow);

        const createCurrencyRow = () => {
            const index = nextCurrencyIndex++;
            const row = document.createElement('div');
            row.className = 'available-currency-row';
            row.style.display = 'grid';
            row.style.gridTemplateColumns = '1fr 1fr 1fr auto';
            row.style.gap = '0.75rem';
            row.style.alignItems = 'center';

            const options = currencyPresetOptions.map((preset) =>
                `<option value="${preset.code}" data-symbol="${preset.symbol}">${preset.code}</option>`
            ).join('');

            row.innerHTML = `
                <select name="available_currencies[${index}][code]" class="form-select currency-code-select">${options}</select>
                <input type="text" name="available_currencies[${index}][symbol]" class="form-input currency-symbol-input" placeholder="Symbol" value="">
                <input type="number" name="available_currencies[${index}][exchange_rate]" class="form-input" placeholder="Exchange rate" step="0.000001" min="0" value="1">
                <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-currency-row aria-label="Remove currency">×</button>
            `;

            bindCurrencyRow(row);
            return row;
        };

        addCurrencyButton.addEventListener('click', () => {
            currencyRows.appendChild(createCurrencyRow());
        });

        currencyRows.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-currency-row]');
            if (!button) {
                return;
            }

            const row = button.closest('.available-currency-row');
            if (row && currencyRows.querySelectorAll('.available-currency-row').length > 1) {
                row.remove();
            }
        });
    }

    const localeRows = document.getElementById('available-locale-rows');
    const addLocaleButton = document.getElementById('add-locale-row');
    let nextLocaleIndex = localeRows ? localeRows.querySelectorAll('.available-locale-row').length : 0;

    const localePresets = @json(array_values($localePresets));

    if (localeRows && addLocaleButton) {
        const bindLocaleRow = (row) => {
            const select = row.querySelector('.locale-code-select');
            const nameInput = row.querySelector('.locale-name-input');
            if (!select || !nameInput) {
                return;
            }

            select.addEventListener('change', () => {
                const option = select.selectedOptions[0];
                if (option?.dataset.name) {
                    nameInput.value = option.dataset.name;
                }
            });
        };

        localeRows.querySelectorAll('.available-locale-row').forEach(bindLocaleRow);

        const createLocaleRow = () => {
            const index = nextLocaleIndex++;
            const row = document.createElement('div');
            row.className = 'available-locale-row';
            row.style.display = 'grid';
            row.style.gridTemplateColumns = '1fr 2fr auto';
            row.style.gap = '0.75rem';
            row.style.alignItems = 'center';

            const options = localePresets.map((preset) =>
                `<option value="${preset.code}" data-name="${preset.name}">${preset.code}</option>`
            ).join('');

            row.innerHTML = `
                <select name="available_locales[${index}][code]" class="form-select locale-code-select">${options}</select>
                <input type="text" name="available_locales[${index}][name]" class="form-input locale-name-input" placeholder="Name" value="${localePresets[0]?.name || ''}">
                <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-locale-row aria-label="Remove language">×</button>
            `;

            bindLocaleRow(row);
            return row;
        };

        addLocaleButton.addEventListener('click', () => {
            localeRows.appendChild(createLocaleRow());
        });

        localeRows.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-locale-row]');
            if (!button) {
                return;
            }

            const row = button.closest('.available-locale-row');
            if (row && localeRows.querySelectorAll('.available-locale-row').length > 1) {
                row.remove();
            }
        });
    }
})();
</script>
@endpush
