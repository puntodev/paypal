<?php

namespace Puntodev\Payments;

use GuzzleHttp\Client as GuzzleHttpClient;
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
                __DIR__ . '/../config/config.php' => config_path('paypal.php'),
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
        $this->app->singleton('paypal', function () {
            $clientKey = config('paypal.client_id');
            $clientSecret = config('paypal.client_secret');
            $useSandbox = config('paypal.use_sandbox');

            return new PayPal(
                new GuzzleHttpClient([
                    'headers' => [
                        'Accept' => 'application/json'
                    ]
                ]),
                $clientKey,
                $clientSecret,
                $useSandbox
            );
        });
    }
}
