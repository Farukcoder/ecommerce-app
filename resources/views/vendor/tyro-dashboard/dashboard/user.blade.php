@extends('tyro-dashboard::layouts.user')

@section('title', 'Dashboard')

@section('breadcrumb')
<span>Dashboard</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Welcome back, {{ $user->name ?? 'User' }}!</h1>
            <p class="page-description" style="font-size: 1rem;">Here's what's happening with your account today.</p>
        </div>
    </div>
</div>

<!-- User Dashboard -->
<!-- <div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">Your Account</h3>
    </div>
    <div class="card-body">
        <div class="user-cell" style="margin-bottom: 1.5rem;">
            <div class="user-cell-avatar" style="width: 64px; height: 64px; font-size: 1.5rem; {{ ($user->profile_photo_path || $user->use_gravatar) ? 'background: none; padding: 0;' : '' }}">
                @if($user->profile_photo_path || ($user->use_gravatar && $user->email))
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div class="user-cell-info">
                <div class="user-cell-name" style="font-size: 1.375rem;">{{ $user->name }}</div>
                <div class="user-cell-email" style="font-size: 0.9375rem;">{{ $user->email }}</div>
            </div>
        </div>

        @if(method_exists($user, 'roles') && $user->roles && $user->roles->count())
        <div style="margin-bottom: 1.5rem;">
            <strong style="font-size: 0.9375rem; color: var(--muted-foreground);">Your Roles:</strong>
            <div class="badge-list" style="margin-top: 0.5rem;">
                @foreach($user->roles as $role)
                    <span class="badge badge-primary" style="font-size: 0.875rem;">{{ $role->name }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <a href="{{ route($dashboardRoute::name('profile')) }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Profile
        </a>
    </div>
</div> -->

<div class="stats-grid">
        <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10V6m0 12v-2m6-6a6 6 0 11-12 0 6 6 0 0112 0z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Monthly Revenue</div>
            <div class="stat-value">$48,230</div>
            <div class="stat-change stat-change-up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17l9-9m0 0H7m9 0v9"></path></svg>
                <span>+12.4% vs last month</span>
            </div>
        </div>
    </div>
        <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 11a4 4 0 100-8 4 4 0 000 8z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M20 8v6"></path><path stroke-linecap="round" stroke-linejoin="round" d="M23 11h-6"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">New Signups</div>
            <div class="stat-value">1,284</div>
            <div class="stat-change stat-change-up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17l9-9m0 0H7m9 0v9"></path></svg>
                <span>+6.1% this week</span>
            </div>
        </div>
    </div>
        <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6"></path><path stroke-linecap="round" stroke-linejoin="round" d="M9 16h6"></path><path stroke-linecap="round" stroke-linejoin="round" d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9l-7-7z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Open Tickets</div>
            <div class="stat-value">42</div>
            <div class="stat-change stat-change-down">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l9 9m0 0V7m0 9H7"></path></svg>
                <span>-3 since yesterday</span>
            </div>
        </div>
    </div>
        <div class="stat-card">
        <div class="stat-icon stat-icon-danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"></path><path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Error Rate</div>
            <div class="stat-value">0.18%</div>
            <div class="stat-change stat-change-up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17l9-9m0 0H7m9 0v9"></path></svg>
                <span>+0.03% today</span>
            </div>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom: 1.5rem;">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Weekly Sales</h3>
            <span class="badge badge-secondary">Bar chart</span>
        </div>
        <div class="card-body">
            <div style="display:flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.875rem; color: var(--muted-foreground);">Total</div>
                    <div style="font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em;">4,350</div>
                </div>
                <div class="badge-list">
                    <span class="badge badge-primary">7 days</span>
                </div>
            </div>

            <div style="border: 1px solid var(--border); border-radius: 10px; padding: 1rem; background: var(--muted);">
                <div style="display:grid; grid-template-columns: repeat(7, 1fr); gap: 0.625rem; align-items: end; height: 180px;">
                                            <div style="display:flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                            <div title="Mon: 420" style="height: 150px; display:flex; align-items:flex-end; position: relative;">
                                <div style="position:absolute; top: 0; left: 0; right: 0; text-align:center; font-size: 0.75rem; color: var(--muted-foreground);">420</div>
                                <div style="width: 100%; height: 42%; background: var(--foreground); border-radius: 8px; border: 1px solid var(--border);"></div>
                            </div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground); text-align:center;">Mon</div>
                        </div>
                                            <div style="display:flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                            <div title="Tue: 610" style="height: 150px; display:flex; align-items:flex-end; position: relative;">
                                <div style="position:absolute; top: 0; left: 0; right: 0; text-align:center; font-size: 0.75rem; color: var(--muted-foreground);">610</div>
                                <div style="width: 100%; height: 61%; background: var(--foreground); border-radius: 8px; border: 1px solid var(--border);"></div>
                            </div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground); text-align:center;">Tue</div>
                        </div>
                                            <div style="display:flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                            <div title="Wed: 510" style="height: 150px; display:flex; align-items:flex-end; position: relative;">
                                <div style="position:absolute; top: 0; left: 0; right: 0; text-align:center; font-size: 0.75rem; color: var(--muted-foreground);">510</div>
                                <div style="width: 100%; height: 51%; background: var(--foreground); border-radius: 8px; border: 1px solid var(--border);"></div>
                            </div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground); text-align:center;">Wed</div>
                        </div>
                                            <div style="display:flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                            <div title="Thu: 820" style="height: 150px; display:flex; align-items:flex-end; position: relative;">
                                <div style="position:absolute; top: 0; left: 0; right: 0; text-align:center; font-size: 0.75rem; color: var(--muted-foreground);">820</div>
                                <div style="width: 100%; height: 82%; background: var(--foreground); border-radius: 8px; border: 1px solid var(--border);"></div>
                            </div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground); text-align:center;">Thu</div>
                        </div>
                                            <div style="display:flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                            <div title="Fri: 760" style="height: 150px; display:flex; align-items:flex-end; position: relative;">
                                <div style="position:absolute; top: 0; left: 0; right: 0; text-align:center; font-size: 0.75rem; color: var(--muted-foreground);">760</div>
                                <div style="width: 100%; height: 76%; background: var(--foreground); border-radius: 8px; border: 1px solid var(--border);"></div>
                            </div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground); text-align:center;">Fri</div>
                        </div>
                                            <div style="display:flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                            <div title="Sat: 540" style="height: 150px; display:flex; align-items:flex-end; position: relative;">
                                <div style="position:absolute; top: 0; left: 0; right: 0; text-align:center; font-size: 0.75rem; color: var(--muted-foreground);">540</div>
                                <div style="width: 100%; height: 54%; background: var(--foreground); border-radius: 8px; border: 1px solid var(--border);"></div>
                            </div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground); text-align:center;">Sat</div>
                        </div>
                                            <div style="display:flex; flex-direction: column; gap: 0.5rem; align-items: stretch;">
                            <div title="Sun: 690" style="height: 150px; display:flex; align-items:flex-end; position: relative;">
                                <div style="position:absolute; top: 0; left: 0; right: 0; text-align:center; font-size: 0.75rem; color: var(--muted-foreground);">690</div>
                                <div style="width: 100%; height: 69%; background: var(--foreground); border-radius: 8px; border: 1px solid var(--border);"></div>
                            </div>
                            <div style="font-size: 0.8125rem; color: var(--muted-foreground); text-align:center;">Sun</div>
                        </div>
                                    </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Traffic (Last 14 days)</h3>
            <span class="badge badge-secondary">SVG chart</span>
        </div>
        <div class="card-body">
            <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.875rem; color: var(--muted-foreground);">Total</div>
                    <div style="font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em;">128,430</div>
                </div>
                <div class="badge-list">
                    <span class="badge badge-primary">Unique</span>
                    <span class="badge badge-success">+9.2%</span>
                </div>
            </div>

            <div style="border: 1px solid var(--border); border-radius: 10px; padding: 1rem; background: var(--muted);">
                <svg viewBox="0 0 600 180" width="100%" height="180" preserveAspectRatio="none" style="display:block; color: var(--primary);">
                    <g opacity="0.35" stroke="currentColor" style="color: var(--muted-foreground);">
                        <path d="M0 150 H600"></path>
                        <path d="M0 110 H600"></path>
                        <path d="M0 70 H600"></path>
                        <path d="M0 30 H600"></path>
                    </g>
                    <path d="M 0 140 C 60 130, 90 115, 120 110 C 170 100, 190 85, 240 90 C 290 95, 320 70, 360 60 C 400 52, 440 64, 480 50 C 520 38, 560 35, 600 30 L 600 170 L 0 170 Z" fill="currentColor" opacity="0.12"></path>
                    <path d="M 0 140 C 60 130, 90 115, 120 110 C 170 100, 190 85, 240 90 C 290 95, 320 70, 360 60 C 400 52, 440 64, 480 50 C 520 38, 560 35, 600 30" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <div style="display:flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.8125rem; color: var(--muted-foreground);">
                    <span>14 days ago</span>
                    <span>Today</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
