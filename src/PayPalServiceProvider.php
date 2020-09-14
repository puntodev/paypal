<?php

namespace Puntodev\Payments;

use Illuminate\Support\ServiceProvider;

class PayPalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/paypal.php' => config_path('paypal.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/paypal.php', 'paypal');

        // Register the main class to use with the facade
        $this->app->singleton('paypal', function ($app) {
            $clientKey = config('paypal.client_id');
            $clientSecret = config('paypal.client_secret');
            $useSandbox = config('paypal.use_sandbox');

            return new PayPal(
                $clientKey,
                $clientSecret,
                $useSandbox
            );
        });
    }
}
