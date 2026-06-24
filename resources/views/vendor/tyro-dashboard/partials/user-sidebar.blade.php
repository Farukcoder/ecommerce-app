<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="{{ route($dashboardRoute::name('index')) }}" class="sidebar-logo">
            @php
                $sidebarLogo = config('tyro-dashboard.branding.sidebar_logo');
                $sidebarLogoSrc = $sidebarLogo && !str_starts_with($sidebarLogo, 'http://') && !str_starts_with($sidebarLogo, 'https://')
                    ? \Illuminate\Support\Facades\Storage::url($sidebarLogo)
                    : $sidebarLogo;
            @endphp
            @if($sidebarLogo)
                <img src="{{ $sidebarLogoSrc }}" alt="{{ $branding['app_name'] ?? config('app.name', 'Laravel') }}" class="sidebar-logo-img">
            @else
                <div class="sidebar-logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            @endif
            <span class="sidebar-logo-text">{{ $branding['app_name'] ?? config('app.name', 'Laravel') }}</span>
        </a>
        @if(config('tyro-dashboard.collapsible_sidebar', false))
        <button class="sidebar-collapse-btn" onclick="toggleSidebarCollapse()" aria-label="Collapse sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        @endif
    </div>
    @if(config('tyro-dashboard.collapsible_sidebar', false))
    <button class="sidebar-expand-btn" onclick="toggleSidebarCollapse()" aria-label="Expand sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
    </button>
    @endif

    <nav class="sidebar-nav sidebar-accordion"
        data-sidebar-accordion
        data-sidebar-accordion-compact="{{ config('tyro-dashboard.branding.sidebar_accordion_compact', false) ? 'true' : 'false' }}"
        data-sidebar-accordion-open-sections="{{ config('tyro-dashboard.branding.sidebar_accordion_open_sections', 1) }}">

        <!-- Main Menu -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.menu') }}</div>

            <a href="{{ route($dashboardRoute::name('index')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('index')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                {{ __('messages.dashboard') }}
            </a>

            @if(config('tyro-dashboard.features.invitation_system', true))
            <a href="{{ route($dashboardRoute::name('invitations.index')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('invitations.index')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                {{ __('messages.my_invitation_link') }}
            </a>
            @endif

            @if(!empty($commonMenuItems))
                @foreach($commonMenuItems as $item)
                    <a href="{{ route($item['route'] ?? '#') }}" class="sidebar-link {{ request()->routeIs($item['route'] ?? '') ? 'active' : '' }}">
                        @if(isset($item['icon']))
                            {!! $item['icon'] !!}
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        @endif
                        {{ $item['title'] ?? 'Menu Item' }}
                    </a>
                @endforeach
            @endif

            @if(!empty($userMenuItems))
                @foreach($userMenuItems as $item)
                    <a href="{{ route($item['route'] ?? '#') }}" class="sidebar-link {{ request()->routeIs($item['route'] ?? '') ? 'active' : '' }}">
                        @if(isset($item['icon']))
                            {!! $item['icon'] !!}
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        @endif
                        {{ $item['title'] ?? 'Menu Item' }}
                    </a>
                @endforeach
            @endif
        </div>

        <!-- Order Management -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.order_management') }}</div>
            <a href="{{ route('orders.index') }}" class="sidebar-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                {{ __('messages.orders') }}
            </a>
        </div>

        <!-- Product Management -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.product_management') }}</div>
            <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') && !request()->routeIs('products.stock') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                {{ __('messages.all_products') }}
            </a>
            <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                {{ __('messages.categories') }}
            </a>
            <a href="{{ route('attributes.index') }}" class="sidebar-link {{ request()->routeIs('attributes.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                {{ __('messages.attributes') }}
            </a>
            <a href="{{ route('brands.index') }}" class="sidebar-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
                {{ __('messages.brands') }}
            </a>
            <a href="{{ route('products.stock') }}" class="sidebar-link {{ request()->routeIs('products.stock') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                {{ __('messages.stock') }}
            </a>
            <a href="{{ route('reviews.index') }}" class="sidebar-link {{ request()->routeIs('reviews.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                {{ __('messages.product_review') }}
            </a>
        </div>

        <!-- Customer Management -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.customer_management') }}</div>
            <a href="{{ route('customers.index') }}" class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ __('messages.customers') }}
            </a>
        </div>

        <!-- Report Management -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.report_management') }}</div>
            <a href="{{ route('reports.sales') }}" class="sidebar-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                {{ __('messages.sales_report') }}
            </a>
        </div>

        <!-- Marketing & Promotions -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.marketing_promotions') }}</div>
            <a href="" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                {{ __('messages.promotion_offers') }}
            </a>
            <a href="" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ __('messages.marketing_analytics') }}
            </a>
        </div>

        <!-- Support & Communication -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.support_communication') }}</div>
            <a href="{{ route('contact-us.index') }}" class="sidebar-link {{ request()->routeIs('contact-us.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ __('messages.contact_us') }}
            </a>
            <a href="{{ route('support-tickets.index') }}" class="sidebar-link {{ request()->routeIs('support-tickets.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                {{ __('messages.ticket') }}
            </a>
        </div>

        <!-- Media -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.media') }}</div>
            <a href="{{ route($dashboardRoute::name('media')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('media*')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                {{ __('messages.media_library') }}
            </a>
        </div>

        @php
            $accessibleResources = [];
            foreach ($allResources ?? config('tyro-dashboard.resources', []) as $key => $resource) {
                $canAccess = true;
                if (isset($resource['roles']) && !empty($resource['roles'])) {
                    $canAccess = false;
                    $user = auth()->user();
                    if ($user && method_exists($user, 'tyroRoleSlugs')) {
                        $userRoles = $user->tyroRoleSlugs();
                        foreach ($resource['roles'] as $role) {
                            if (in_array($role, $userRoles)) {
                                $canAccess = true;
                                break;
                            }
                        }
                        if (!$canAccess && isset($resource['readonly']) && !empty($resource['readonly'])) {
                            foreach ($resource['readonly'] as $role) {
                                if (in_array($role, $userRoles)) {
                                    $canAccess = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                if ($canAccess) {
                    $accessibleResources[$key] = $resource;
                }
            }
        @endphp

        @if(!empty($accessibleResources))
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.resources') }}</div>
            @foreach($accessibleResources as $key => $resource)
                <a href="{{ route($dashboardRoute::name('resources.index'), $key) }}" class="sidebar-link {{ request()->is('*resources/'.$key.'*') ? 'active' : '' }}">
                    @if(isset($resource['icon']))
                        {!! $resource['icon'] !!}
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    @endif
                    {{ $resource['title'] }}
                </a>
            @endforeach
        </div>
        @endif

        @if(!config('tyro-dashboard.disable_examples', false) && !app()->environment('production'))
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('messages.examples') }}</div>
            <a href="{{ route($dashboardRoute::name('components')) }}" class="sidebar-link {{ (request()->routeIs($dashboardRoute::pattern('components')) || request()->routeIs($dashboardRoute::pattern('examples.components'))) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 6a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2V6z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 15a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2v-3z" />
                </svg>
                {{ __('messages.dashboard_components') }}
            </a>

            <a href="{{ route($dashboardRoute::name('widgets')) }}" class="sidebar-link {{ (request()->routeIs($dashboardRoute::pattern('widgets')) || request()->routeIs($dashboardRoute::pattern('examples.widgets'))) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5h6v6H5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 13h6v6h-6z" />
                </svg>
                {{ __('messages.widgets') }}
            </a>

            @if(class_exists('HasinHayder\\TyroDashboardComponents\\TyroDashboardComponentsServiceProvider'))
            <a href="{{ route($dashboardRoute::name('x-components')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('x-components')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                {{ __('messages.form_components') }}
            </a>
            @endif
        </div>
        @endif
    </nav>
</aside>
