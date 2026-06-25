@php
    $systemSettingForTopbar = \App\Models\SystemSetting::query()->latest('id')->first();
    $availableLocalesForTopbar = $systemSettingForTopbar ? ($systemSettingForTopbar->available_locales ?? []) : [
        ['code' => 'en', 'name' => 'English'],
        ['code' => 'bn', 'name' => 'Bangla'],
    ];
    $currentLocaleForTopbar = app()->getLocale();
    $currentLocaleNameForTopbar = collect($availableLocalesForTopbar)->firstWhere('code', $currentLocaleForTopbar)['name'] ?? strtoupper($currentLocaleForTopbar);

    // Notification bell — recent pending orders for admin
    $topbarRecentOrders = \App\Models\Order::query()
        ->with('customer:id,name,email')
        ->latest()
        ->limit(8)
        ->get(['id', 'order_number', 'customer_id', 'status', 'total_amount', 'created_at']);
    $topbarNewOrdersCount = \App\Models\Order::query()->where('status', 'pending')->count();
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

        <!-- Notification Bell -->
        <div class="notif-dropdown" id="notifDropdown" style="position: relative;">
            <button type="button" class="topbar-btn notif-bell-btn" id="notifBellBtn"
                onclick="toggleNotifDropdown()" aria-label="Notifications"
                style="position: relative;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 1.25rem; height: 1.25rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if($topbarNewOrdersCount > 0)
                <span id="notifBadge" style="
                    position: absolute; top: 2px; right: 2px;
                    background: #ef4444; color: #fff;
                    font-size: 0.65rem; font-weight: 700; line-height: 1;
                    min-width: 1.1rem; height: 1.1rem;
                    border-radius: 9999px;
                    display: flex; align-items: center; justify-content: center;
                    padding: 0 3px;
                    border: 2px solid var(--sidebar-background, #fff);
                    pointer-events: none;
                ">{{ $topbarNewOrdersCount > 99 ? '99+' : $topbarNewOrdersCount }}</span>
                @endif
            </button>

            <!-- Notification Dropdown Panel -->
            <div id="notifPanel" style="
                display: none;
                position: absolute; right: 0; top: calc(100% + 8px);
                width: 22rem; max-height: 26rem;
                background: var(--card); border: 1px solid var(--border);
                border-radius: 0.75rem;
                box-shadow: 0 10px 40px rgba(0,0,0,0.15);
                z-index: 9999; overflow: hidden;
            ">
                <!-- Header -->
                <div style="
                    display: flex; align-items: center; justify-content: space-between;
                    padding: 0.875rem 1rem;
                    border-bottom: 1px solid var(--border);
                ">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 1rem; height: 1rem; color: var(--primary);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span style="font-weight: 600; font-size: 0.9rem;">Notifications</span>
                        @if($topbarNewOrdersCount > 0)
                        <span style="
                            background: #ef4444; color: #fff;
                            font-size: 0.65rem; font-weight: 700;
                            padding: 1px 6px; border-radius: 9999px;
                        ">{{ $topbarNewOrdersCount }} new</span>
                        @endif
                    </div>
                    <a href="{{ route('orders.index') }}"
                        style="font-size: 0.75rem; color: var(--primary); text-decoration: none; font-weight: 500;"
                        onclick="closeNotifDropdown()">
                        View all
                    </a>
                </div>

                <!-- Order List -->
                <div style="overflow-y: auto; max-height: 20rem;">
                    @forelse($topbarRecentOrders as $topbarOrder)
                    <a href="{{ route('orders.show', $topbarOrder->id) }}"
                        onclick="closeNotifDropdown()"
                        style="
                            display: flex; align-items: flex-start; gap: 0.75rem;
                            padding: 0.75rem 1rem;
                            border-bottom: 1px solid var(--border);
                            text-decoration: none;
                            transition: background 0.15s;
                            {{ $topbarOrder->status === 'pending' ? 'background: color-mix(in srgb, var(--primary) 5%, transparent);' : '' }}
                        "
                        onmouseover="this.style.background='color-mix(in srgb, var(--primary) 8%, transparent)'"
                        onmouseout="this.style.background='{{ $topbarOrder->status === 'pending' ? 'color-mix(in srgb, var(--primary) 5%, transparent)' : 'transparent' }}'">

                        <!-- Status dot -->
                        <span style="
                            margin-top: 3px; flex-shrink: 0;
                            width: 0.55rem; height: 0.55rem; border-radius: 50%;
                            background: {{ match($topbarOrder->status) {
                                'pending'    => '#f59e0b',
                                'processing' => '#3b82f6',
                                'shipped'    => '#8b5cf6',
                                'delivered'  => '#10b981',
                                'cancelled'  => '#ef4444',
                                default      => '#6b7280'
                            } }};
                        "></span>

                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 0.5rem;">
                                <span style="font-weight: 600; font-size: 0.8rem; color: var(--foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $topbarOrder->order_number }}
                                </span>
                                <span style="font-size: 0.7rem; color: var(--muted-foreground); white-space: nowrap; flex-shrink: 0;">
                                    {{ $topbarOrder->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 1px;">
                                {{ $topbarOrder->customer?->name ?? 'Guest' }}
                                &middot;
                                <span style="font-weight: 500; color: var(--foreground);">
                                    ৳{{ number_format((float)$topbarOrder->total_amount, 2) }}
                                </span>
                            </div>
                            <span style="
                                display: inline-block; margin-top: 3px;
                                font-size: 0.65rem; font-weight: 600; text-transform: uppercase;
                                padding: 1px 6px; border-radius: 4px; letter-spacing: 0.03em;
                                color: {{ match($topbarOrder->status) {
                                    'pending'    => '#92400e',
                                    'processing' => '#1e40af',
                                    'shipped'    => '#5b21b6',
                                    'delivered'  => '#065f46',
                                    'cancelled'  => '#991b1b',
                                    default      => '#374151'
                                } }};
                                background: {{ match($topbarOrder->status) {
                                    'pending'    => '#fef3c7',
                                    'processing' => '#dbeafe',
                                    'shipped'    => '#ede9fe',
                                    'delivered'  => '#d1fae5',
                                    'cancelled'  => '#fee2e2',
                                    default      => '#f3f4f6'
                                } }};
                            ">
                                {{ ucfirst($topbarOrder->status) }}
                            </span>
                        </div>
                    </a>
                    @empty
                    <div style="padding: 2rem 1rem; text-align: center; color: var(--muted-foreground); font-size: 0.85rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 2rem; height: 2rem; margin: 0 auto 0.5rem; opacity: 0.4;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        No orders yet
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <style>
            .notif-bell-btn:focus-visible { outline: none; }
            #notifPanel a:last-child { border-bottom: none; }
        </style>

        <script>
            function toggleNotifDropdown() {
                const panel = document.getElementById('notifPanel');
                const isOpen = panel.style.display !== 'none';
                // Close all other dropdowns first
                document.querySelectorAll('.user-dropdown-menu').forEach(m => m.style.display = 'none');
                panel.style.display = isOpen ? 'none' : 'block';
            }
            function closeNotifDropdown() {
                document.getElementById('notifPanel').style.display = 'none';
            }
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                const dropdown = document.getElementById('notifDropdown');
                if (dropdown && !dropdown.contains(e.target)) {
                    closeNotifDropdown();
                }
            }, true);
        </script>

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
