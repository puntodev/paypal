<?php

namespace Puntodev\Payments;

use Illuminate\Support\ServiceProvider;

class PayPalServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/paypal.php' => config_path('paypal.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/paypal.php', 'paypal');

        $this->app->singleton(PayPal::class, function ($app) {
            return new PayPalClient(
                config('paypal.client_id'),
                config('paypal.client_secret'),
                config('paypal.use_sandbox'),
            );
        });
        $this->app->alias(PayPal::class, 'paypal');
    }
}
