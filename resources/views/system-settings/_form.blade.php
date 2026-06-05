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
})();
</script>
@endpush
