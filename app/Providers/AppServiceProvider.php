<?php

namespace App\Providers;

use App\Models\Trade;
use App\Observers\TradeObserver;
use App\Services\AnalyticsService;
use App\Services\TradeCalculationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TradeCalculationService::class);
        $this->app->singleton(AnalyticsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Trade::observe(TradeObserver::class);
    }
}
