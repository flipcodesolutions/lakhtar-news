<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Kreait\Laravel\Firebase\FirebaseProjectManager::class,
            \App\Firebase\FirebaseProjectManager::class
        );

        $this->app->alias(
            \Kreait\Laravel\Firebase\FirebaseProjectManager::class,
            'firebase.manager'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
