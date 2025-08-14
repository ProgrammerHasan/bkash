<?php

namespace ProgrammerHasan\Bkash;

use Illuminate\Support\ServiceProvider;
use ProgrammerHasan\Bkash\Products\BkashPayment;
use ProgrammerHasan\Bkash\App\Exceptions\BkashExceptionHandler;
use ProgrammerHasan\Bkash\App\Service\BkashAuthService;

class BkashServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/config/bkash.php", "bkash");

        $this->app->bind("BkashPayment", function () {
            return new BkashPayment();
        });

        $this->loadViewsFrom(__DIR__ . '/Views', 'bkash');

        $this->app->singleton(BkashAuthService::class, function ($app) {
            return new BkashAuthService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . "/config/bkash.php" => config_path("bkash.php")
        ], 'config');
        $this->publishes([
            __DIR__.'/app/Controllers/BkashPaymentController.php' => app_path('Http/Controllers/BkashPaymentController.php'),
        ],'controllers');
        $this->publishes([
            __DIR__ . '/routes/bkash.php' => base_path('routes/bkash.php'),
        ], 'routes');

        $this->loadRoutesFrom(__DIR__ . '/routes/bkash.php');

        $this->renderBkashException();
    }

    protected function renderBkashException(): void
    {
        $this->app->bind(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            BkashExceptionHandler::class
        );
    }

}
