<?php

namespace App\Providers;

use App\Services\GeminiService;
use App\Services\TokenService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GeminiService::class, function ($app) {
            return new GeminiService();
        });
 
        // Bind TokenService
        $this->app->singleton(TokenService::class, function ($app) {
            return new TokenService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
 
        // Pagination menggunakan simple bootstrap-style
        \Illuminate\Pagination\Paginator::useBootstrapFive();
    }
}
