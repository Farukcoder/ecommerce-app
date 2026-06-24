@php
    $systemSettingForTopbar = \App\Models\SystemSetting::query()->latest('id')->first();
    $availableLocalesForTopbar = $systemSettingForTopbar ? ($systemSettingForTopbar->available_locales ?? []) : [
        ['code' => 'en', 'name' => 'English'],
        ['code' => 'bn', 'name' => 'Bangla'],
    ];
    $currentLocaleForTopbar = app()->getLocale();
    $currentLocaleNameForTopbar = collect($availableLocalesForTopbar)->firstWhere('code', $currentLocaleForTopbar)['name'] ?? strtoupper($currentLocaleForTopbar);
@endphp

<header class="topbar">
    <div class="topbar-left">
        <button type="button" class="mobile-menu-btn" onclick="toggleSidebar()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <nav class="breadcrumb">
            @yield('breadcrumb')
        </nav>
    </div>

    <div class="topbar-right">
        <!-- Language Dropdown -->
        <div class="user-dropdown" id="languageDropdown">
            <button type="button" class="user-dropdown-btn" onclick="toggleLanguageDropdown()" aria-label="Select language" style="padding: 0.5rem 0.75rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--foreground)" stroke-width="2" style="width: 1.25rem; height: 1.25rem; margin-right: 0.25rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                </svg>
                <span class="user-name" style="font-size: 0.875rem; margin-right: 0.25rem;">{{ $currentLocaleNameForTopbar }}</span>
                <svg class="user-dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 1rem; height: 1rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="user-dropdown-menu">
                @foreach($availableLocalesForTopbar as $locale)
                    <a href="?lang={{ $locale['code'] }}" class="dropdown-item" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                        <span>{{ $locale['name'] }}</span>
                        @if($locale['code'] === $currentLocaleForTopbar)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 1rem; height: 1rem; color: var(--primary);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Theme Toggle -->
        <button type="button" class="topbar-btn" onclick="toggleTheme()" aria-label="Toggle theme">
            <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        <!-- User Dropdown -->
        <div class="user-dropdown" id="userDropdown">
            <button type="button" class="user-dropdown-btn" onclick="toggleUserDropdown()">
                <div class="user-avatar" style="{{ ((method_exists($user, 'hasProfilePhotoColumn') && $user->hasProfilePhotoColumn() && $user->profile_photo_path) || (method_exists($user, 'hasGravatarColumn') && $user->hasGravatarColumn() && $user->use_gravatar)) ? 'background: none; padding: 0;' : '' }}">
                    @if((method_exists($user, 'hasProfilePhotoColumn') && $user->hasProfilePhotoColumn() && $user->profile_photo_path) || (method_exists($user, 'hasGravatarColumn') && $user->hasGravatarColumn() && $user->use_gravatar && $user->email))
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <div class="user-info">
                    <div class="user-name">{{ $user->name ?? 'User' }}</div>
                    <div class="user-role">
                        @if(method_exists($user, 'roles') && $user->roles->count())
                            {{ $user->roles->first()->name }}
                        @else
                            User
                        @endif
                    </div>
                </div>
                <svg class="user-dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div class="user-dropdown-menu">
                <a href="{{ route($dashboardRoute::name('profile')) }}" class="dropdown-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ __('messages.my_profile') }}
                </a>
                <div class="dropdown-divider"></div>
                @if(session('impersonator_id'))
                    <form action="{{ route($dashboardRoute::name('leave-impersonation')) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="dropdown-item dropdown-item-danger" style="width: 100%; text-align: left; border: none; background: none; cursor: pointer;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            {{ __('messages.exit_impersonation') }}
                        </button>
                    </form>
                @else
                    <form action="{{ route('tyro-login.logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="dropdown-item dropdown-item-danger" style="width: 100%; text-align: left; border: none; background: none; cursor: pointer;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            {{ __('messages.logout') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</header>
