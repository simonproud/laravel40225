<?php

namespace App\Providers;

use App\Queue\Connectors\AmqpConnector;
use App\Services\BalanceService;
use App\Services\OperationService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BalanceService::class, function ($app) {
            return new BalanceService();
        });
        $this->app->singleton(OperationService::class, function ($app) {
            return new OperationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Queue::extend('amqp', function () {
            return new AmqpConnector;
        });
        Vite::prefetch(concurrency: 3);
    }
}
