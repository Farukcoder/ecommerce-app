@extends('tyro-dashboard::layouts.admin')

@section('title', __('messages.header_setting_page_title'))

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">{{ __('messages.dashboard') }}</a>
<span class="breadcrumb-separator">/</span>
<span>{{ __('messages.header_setting_page_title') }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ __('messages.header_setting_page_title') }}</h1>
            <p class="page-description">{{ __('messages.header_setting_description') }}</p>
        </div>
        @if($headerSetting)
            <a href="{{ route('header-settings.edit', $headerSetting) }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                {{ __('messages.edit_setting') }}
            </a>
        @else
            <a href="{{ route('header-settings.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                {{ __('messages.create_setting') }}
            </a>
        @endif
    </div>
</div>

@if($headerSetting)
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">{{ __('messages.sticky_header') }}</div>
                <div class="stat-value" style="font-size: 1.1rem;">{{ $headerSetting->enable_sticky_header ? __('messages.enabled') : __('messages.disabled') }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">{{ __('messages.language_switcher') }}</div>
                <div class="stat-value" style="font-size: 1.1rem;">{{ $headerSetting->show_language_switcher ? __('messages.shown') : __('messages.hidden') }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">{{ __('messages.nav_items') }}</div>
                <div class="stat-value" style="font-size: 1.1rem;">{{ collect($headerSetting->header_nav_menu ?? [])->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">{{ __('messages.last_updated') }}</div>
                <div class="stat-value" style="font-size: 1.1rem;">{{ $headerSetting->updated_at?->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    <div class="grid-2" style="align-items:start;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('messages.header_logo_switchers') }}</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.header_logo') }}</label>
                    @if($headerSetting->header_logo_url)
                        <img src="{{ $headerSetting->header_logo_url }}" alt="{{ __('messages.header_logo') }}" style="max-width: 220px; max-height: 80px; object-fit: contain; border: 1px solid var(--border); border-radius: 10px; padding: 0.5rem; background: var(--background);">
                    @else
                        <p class="form-hint">{{ __('messages.no_header_logo') }}</p>
                    @endif
                </div>

                <div class="badge-list">
                    <span class="badge {{ $headerSetting->show_language_switcher ? 'badge-success' : 'badge-secondary' }}">{{ __('messages.language') }} {{ $headerSetting->show_language_switcher ? __('messages.on') : __('messages.off') }}</span>
                    <span class="badge {{ $headerSetting->show_currency_switcher ? 'badge-success' : 'badge-secondary' }}">{{ __('messages.currency') }} {{ $headerSetting->show_currency_switcher ? __('messages.on') : __('messages.off') }}</span>
                    <span class="badge {{ $headerSetting->enable_sticky_header ? 'badge-primary' : 'badge-secondary' }}">{{ __('messages.sticky') }} {{ $headerSetting->enable_sticky_header ? __('messages.on') : __('messages.off') }}</span>
                </div>

                <div style="margin-top: 1rem; display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                    <div>
                        <label class="form-label" style="margin-bottom:0.25rem;">{{ __('messages.top_header_bg') }}</label>
                        <span class="badge badge-secondary">{{ $headerSetting->top_header_bg_color }}</span>
                    </div>
                    <div>
                        <label class="form-label" style="margin-bottom:0.25rem;">{{ __('messages.bottom_header_bg') }}</label>
                        <span class="badge badge-secondary">{{ $headerSetting->bottom_header_bg_color }}</span>
                    </div>
                    <div>
                        <label class="form-label" style="margin-bottom:0.25rem;">{{ __('messages.top_text') }}</label>
                        <span class="badge badge-secondary">{{ $headerSetting->top_header_text_color }}</span>
                    </div>
                    <div>
                        <label class="form-label" style="margin-bottom:0.25rem;">{{ __('messages.bottom_text') }}</label>
                        <span class="badge badge-secondary">{{ $headerSetting->bottom_header_text_color }}</span>
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <label class="form-label" style="margin-bottom:0.25rem;">{{ __('messages.help_line_number') }}</label>
                    <p style="font-size:0.875rem; color:var(--muted-foreground);">{{ $headerSetting->help_line_number ?: '—' }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('messages.header_nav_menu') }}</h3>
            </div>
            <div class="card-body">
                @if(!empty($headerSetting->header_nav_menu))
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.label') }}</th>
                                    <th>{{ __('messages.url') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($headerSetting->header_nav_menu as $item)
                                    <tr>
                                        <td>{{ $item['label'] ?? '—' }}</td>
                                        <td>{{ $item['url'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="form-hint">{{ __('messages.no_nav_items') }}</p>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="empty-state">
            <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3" />
            </svg>
            <h3 class="empty-state-title">{{ __('messages.no_header_setting') }}</h3>
            <p class="empty-state-description">{{ __('messages.create_header_setting_hint') }}</p>
            <a href="{{ route('header-settings.create') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
                {{ __('messages.create_header_setting') }}
            </a>
        </div>
    </div>
@endif
@endsection
