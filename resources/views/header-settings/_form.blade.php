@php
    $navMenu = old('header_nav_menu', $headerSetting->header_nav_menu ?? []);
    if (!is_array($navMenu) || empty($navMenu)) {
        $navMenu = [
            ['label' => 'Home', 'url' => '/'],
        ];
    }

    $fieldValue = function (string $key, $default = '') use ($headerSetting) {
        return old($key, $headerSetting->{$key} ?? $default);
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
                <h3 class="card-title">Header Branding</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="header_logo" class="form-label">Header Logo</label>
                    <input type="file" id="header_logo" name="header_logo" class="form-input" style="padding:0.5rem;" accept="image/*,.svg">
                    <p class="form-hint">Minimum dimensions required: 224px width x 43px height.</p>
                    @error('header_logo')<span class="form-error">{{ $message }}</span>@enderror

                    @if(!empty($headerSetting->header_logo))
                        <div style="margin-top:0.75rem; display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                            <div style="border:1px solid var(--border); border-radius:10px; padding:0.5rem; background:var(--background);">
                                <img src="{{ $headerSetting->header_logo_url }}" alt="Header Logo" style="max-height: 72px; max-width: 260px; object-fit: contain;">
                            </div>
                            <span class="badge badge-secondary">Current file</span>
                        </div>
                    @endif
                </div>

                <div class="form-check" style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                    <input type="checkbox" id="show_language_switcher" name="show_language_switcher" value="1" {{ $fieldValue('show_language_switcher', true) ? 'checked' : '' }}>
                    <label for="show_language_switcher" style="margin-bottom:0;">Show Language Switcher?</label>
                </div>

                <div class="form-check" style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                    <input type="checkbox" id="show_currency_switcher" name="show_currency_switcher" value="1" {{ $fieldValue('show_currency_switcher', true) ? 'checked' : '' }}>
                    <label for="show_currency_switcher" style="margin-bottom:0;">Show Currency Switcher?</label>
                </div>

                <div class="form-check" style="display:flex; align-items:center; gap:0.5rem;">
                    <input type="checkbox" id="enable_sticky_header" name="enable_sticky_header" value="1" {{ $fieldValue('enable_sticky_header', true) ? 'checked' : '' }}>
                    <label for="enable_sticky_header" style="margin-bottom:0;">Enable sticky header?</label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Header Colors</h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="top_header_bg_color" class="form-label">top_header_bg_color</label>
                        <input type="text" id="top_header_bg_color" name="top_header_bg_color" class="form-input @error('top_header_bg_color') is-invalid @enderror" value="{{ $fieldValue('top_header_bg_color', '#FFFFFF') }}" required>
                        @error('top_header_bg_color')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="bottom_header_bg_color" class="form-label">bottom_header_bg_color</label>
                        <input type="text" id="bottom_header_bg_color" name="bottom_header_bg_color" class="form-input @error('bottom_header_bg_color') is-invalid @enderror" value="{{ $fieldValue('bottom_header_bg_color', '#FFFFFF') }}" required>
                        @error('bottom_header_bg_color')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row" style="margin-top: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="top_header_text_color" class="form-label">top_header_text_color</label>
                        <input type="text" id="top_header_text_color" name="top_header_text_color" class="form-input @error('top_header_text_color') is-invalid @enderror" value="{{ $fieldValue('top_header_text_color', '#000000') }}" required>
                        @error('top_header_text_color')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="bottom_header_text_color" class="form-label">bottom_header_text_color</label>
                        <input type="text" id="bottom_header_text_color" name="bottom_header_text_color" class="form-input @error('bottom_header_text_color') is-invalid @enderror" value="{{ $fieldValue('bottom_header_text_color', '#000000') }}" required>
                        @error('bottom_header_text_color')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1rem; margin-bottom: 0;">
                    <label for="help_line_number" class="form-label">Help line number</label>
                    <input type="text" id="help_line_number" name="help_line_number" class="form-input @error('help_line_number') is-invalid @enderror" value="{{ $fieldValue('help_line_number') }}" placeholder="Help line number">
                    @error('help_line_number')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Header Nav Menu</h3>
        </div>
        <div class="card-body">
            <div id="header-nav-menu-rows" style="display:flex; flex-direction:column; gap:0.75rem;">
                @foreach($navMenu as $index => $item)
                    <div class="header-nav-menu-row" style="display:grid; grid-template-columns: 1fr 2fr auto; gap:0.75rem; align-items:center;">
                        <input type="text" name="header_nav_menu[{{ $index }}][label]" class="form-input" placeholder="Label" value="{{ $item['label'] ?? '' }}">
                        <input type="text" name="header_nav_menu[{{ $index }}][url]" class="form-input" placeholder="https://demo.example.com/..." value="{{ $item['url'] ?? '' }}">
                        <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-header-nav-row aria-label="Remove menu item">×</button>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:0.75rem;">
                <button type="button" class="btn btn-secondary btn-sm" id="add-header-nav-row">Add New</button>
            </div>
        </div>
    </div>

    <div style="margin-top: 1.5rem; display:flex; justify-content:flex-end; gap:0.75rem; flex-wrap:wrap;">
        <a href="{{ route('header-settings.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
<script>
(() => {
    const rows = document.getElementById('header-nav-menu-rows');
    const addButton = document.getElementById('add-header-nav-row');
    let nextIndex = rows ? rows.querySelectorAll('.header-nav-menu-row').length : 0;

    if (!rows || !addButton) {
        return;
    }

    const createRow = (label = '', url = '') => {
        const index = nextIndex++;
        const row = document.createElement('div');
        row.className = 'header-nav-menu-row';
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '1fr 2fr auto';
        row.style.gap = '0.75rem';
        row.style.alignItems = 'center';
        row.innerHTML = `
            <input type="text" name="header_nav_menu[${index}][label]" class="form-input" placeholder="Label" value="${label.replace(/"/g, '&quot;')}">
            <input type="text" name="header_nav_menu[${index}][url]" class="form-input" placeholder="https://demo.example.com/..." value="${url.replace(/"/g, '&quot;')}">
            <button type="button" class="btn btn-ghost" style="color: var(--danger);" data-remove-header-nav-row aria-label="Remove menu item">×</button>
        `;
        return row;
    };

    addButton.addEventListener('click', () => {
        rows.appendChild(createRow());
    });

    rows.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-header-nav-row]');
        if (!button) {
            return;
        }

        const row = button.closest('.header-nav-menu-row');
        if (row && rows.querySelectorAll('.header-nav-menu-row').length > 1) {
            row.remove();
        }
    });
})();
</script>
@endpush
