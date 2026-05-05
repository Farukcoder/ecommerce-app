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
        // Share $dashboardRoute with all product views (same as tyro-dashboard does for its own views)
        View::composer('product.*', function ($view) {
            $view->with('dashboardRoute', DashboardRoute::class);
            $view->with('user', auth()->user());
        });
    }
}
