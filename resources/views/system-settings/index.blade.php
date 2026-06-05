@extends('tyro-dashboard::layouts.admin')

@section('title', 'System Setting')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>System Setting</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">System Setting</h1>
            <p class="page-description">Manage the core storefront identity, brand colors and banner assets.</p>
        </div>
        @if($systemSetting)
            <a href="{{ route('system-settings.edit', $systemSetting) }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Setting
            </a>
        @else
            <a href="{{ route('system-settings.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                Create Setting
            </a>
        @endif
    </div>
</div>

@if($systemSetting)
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">System Name</div>
                <div class="stat-value" style="font-size: 1.1rem;">{{ $systemSetting->system_name }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Frontend Name</div>
                <div class="stat-value" style="font-size: 1.1rem;">{{ $systemSetting->frontend_website_name }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Image Format</div>
                <div class="stat-value" style="font-size: 1.1rem; text-transform: uppercase;">{{ $systemSetting->uploaded_image_format }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Last Updated</div>
                <div class="stat-value" style="font-size: 1.1rem;">{{ $systemSetting->updated_at?->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    <div class="grid-2" style="align-items:start;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Brand Assets</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Site Icon</label>
                    @if($systemSetting->site_icon_url)
                        <img src="{{ $systemSetting->site_icon_url }}" alt="Site Icon" style="max-width: 120px; max-height: 120px; object-fit: contain; border: 1px solid var(--border); border-radius: 10px; padding: 0.5rem; background: var(--background);">
                    @else
                        <p class="form-hint">No icon uploaded yet.</p>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">System Logo - White</label>
                    @if($systemSetting->system_logo_white_url)
                        <img src="{{ $systemSetting->system_logo_white_url }}" alt="System Logo White" style="max-width: 260px; max-height: 80px; object-fit: contain; border: 1px solid var(--border); border-radius: 10px; padding: 0.5rem; background: var(--muted);">
                    @else
                        <p class="form-hint">No white logo uploaded yet.</p>
                    @endif
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">System Logo - Black</label>
                    @if($systemSetting->system_logo_black_url)
                        <img src="{{ $systemSetting->system_logo_black_url }}" alt="System Logo Black" style="max-width: 260px; max-height: 80px; object-fit: contain; border: 1px solid var(--border); border-radius: 10px; padding: 0.5rem; background: var(--background);">
                    @else
                        <p class="form-hint">No black logo uploaded yet.</p>
                    @endif
                </div>

                <div class="form-group" style="margin-bottom:0; margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid var(--border);">
                    <label class="form-label">Product Default Image</label>
                    @if($systemSetting->product_default_image_url)
                        <img src="{{ $systemSetting->product_default_image_url }}" alt="Product Default Image" style="max-width: 120px; max-height: 120px; object-fit: contain; border: 1px solid var(--border); border-radius: 10px; padding: 0.5rem; background: var(--background);">
                    @else
                        <p class="form-hint">No product default image uploaded yet.</p>
                    @endif
                </div>
                @if($systemSetting->flash_deal_page_banner_large_url || $systemSetting->flash_deal_page_banner_small_url)
                    <div style="margin-top: 1.25rem;">
                        <h4 style="font-size: 0.9375rem; margin-bottom: 0.75rem; color: var(--foreground);">Flash Deal / About Banners</h4>
                        <div class="grid-2">
                            @if($systemSetting->flash_deal_page_banner_large_url)
                                <div style="border: 1px solid var(--border); border-radius: 10px; padding: 0.75rem; background: var(--background);">
                                    <div style="font-size: 0.8125rem; color: var(--muted-foreground); margin-bottom: 0.5rem;">Large Banner</div>
                                    <img src="{{ $systemSetting->flash_deal_page_banner_large_url }}" alt="Flash Deal Large Banner" style="width: 100%; max-width: 100%; height: auto; border-radius: 8px; object-fit: cover;">
                                </div>
                            @endif
                            @if($systemSetting->flash_deal_page_banner_small_url)
                                <div style="border: 1px solid var(--border); border-radius: 10px; padding: 0.75rem; background: var(--background);">
                                    <div style="font-size: 0.8125rem; color: var(--muted-foreground); margin-bottom: 0.5rem;">Small Banner</div>
                                    <img src="{{ $systemSetting->flash_deal_page_banner_small_url }}" alt="Flash Deal Small Banner" style="width: 100%; max-width: 100%; height: auto; border-radius: 8px; object-fit: cover;">
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Settings Summary</h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th style="width: 45%;">System Name</th>
                                <td>{{ $systemSetting->system_name }}</td>
                            </tr>
                            <tr>
                                <th>Frontend Website Name</th>
                                <td>{{ $systemSetting->frontend_website_name }}</td>
                            </tr>
                            <tr>
                                <th>Site Motto</th>
                                <td>{{ $systemSetting->site_motto ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Homepage Hero Badge</th>
                                <td>{{ $systemSetting->hero_badge_text ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Homepage Hero Heading</th>
                                <td>{{ $systemSetting->hero_heading ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Homepage Hero Description</th>
                                <td>{{ $systemSetting->hero_description ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Uploaded Image Format</th>
                                <td>{{ strtoupper($systemSetting->uploaded_image_format) }}</td>
                            </tr>
                            <tr>
                                <th>Website Base Color</th>
                                <td><span class="badge badge-secondary">{{ $systemSetting->website_base_color }}</span></td>
                            </tr>
                            <tr>
                                <th>Website Hover Color</th>
                                <td><span class="badge badge-secondary">{{ $systemSetting->website_base_hover_color }}</span></td>
                            </tr>
                            <tr>
                                <th>Secondary Color</th>
                                <td><span class="badge badge-secondary">{{ $systemSetting->website_secondary_base_color }}</span></td>
                            </tr>
                            <tr>
                                <th>Secondary Hover Color</th>
                                <td><span class="badge badge-secondary">{{ $systemSetting->website_secondary_base_hover_color }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">About Us Section</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                @if($systemSetting->about_hero_heading || $systemSetting->about_mission_heading)
                    <div class="table-container">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 45%;">About Hero Heading</th>
                                    <td>{{ $systemSetting->about_hero_heading ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>About Hero Description</th>
                                    <td>{{ \Illuminate\Support\Str::limit($systemSetting->about_hero_description, 120) ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Mission Heading</th>
                                    <td>{{ $systemSetting->about_mission_heading ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Values</th>
                                    <td>{{ count($systemSetting->about_values ?? []) }} item(s)</td>
                                </tr>
                                <tr>
                                    <th>Team Members</th>
                                    <td>{{ count($systemSetting->about_team_members ?? []) }} member(s)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="empty-state">
            <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3" />
            </svg>
            <h3 class="empty-state-title">No system setting found</h3>
            <p class="empty-state-description">Create the initial system setting to configure the storefront identity and banner assets.</p>
            <a href="{{ route('system-settings.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                Create System Setting
            </a>
        </div>
    </div>
@endif
@endsection
