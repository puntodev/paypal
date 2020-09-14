<?php

namespace Tests;

use Puntodev\Payments\PayPal;
use Puntodev\Payments\PayPalApi;

class PaypalTest extends TestCase
{
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

        $this->assertInstanceOf(PayPalApi::class, $client);
    }

    /** @test */
    public function with_credentials()
    {
        /** @var PayPal $paypal */
        $paypal = $this->app->make('paypal');

        $client = $paypal->withCredentials('A', 'B');

        $this->assertInstanceOf(PayPalApi::class, $client);
    }
}
