<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use Puntodev\Payments\PayPal;
use Puntodev\Payments\PayPalApiWrapper;
use Puntodev\Payments\PayPalServiceProvider;

class PaypalTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [PayPalServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('paypal.client_id', 'PAYPAL_ID');
        $app['config']->set('paypal.client_secret', 'PAYPAL_SECRET');
        $app['config']->set('paypal.use_sandbox', 'true');
    }

    /** @test */
    public function default_client()
    {
        /** @var PayPal $paypal */
        $paypal = $this->app->make('paypal');

        $client = $paypal->defaultClient();

        $this->assertInstanceOf(PayPalApiWrapper::class, $client);
    }

    /** @test */
    public function with_credentials()
    {

        /** @var PayPal $paypal */
        $paypal = $this->app->make('paypal');

        $client = $paypal->withCredentials('A', 'B');

        $this->assertInstanceOf(PayPalApiWrapper::class, $client);
    }
}
