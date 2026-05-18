<?php

namespace App\Providers;

use HasinHayder\TyroDashboard\Support\DashboardRoute;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share $dashboardRoute with all views so custom views that extend
        // tyro-dashboard layouts (e.g. products/index, products/create) can
        // call $dashboardRoute::name() and $dashboardRoute::pattern() helpers.
        View::share('dashboardRoute', DashboardRoute::class);
    }
}
