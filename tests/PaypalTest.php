<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use Puntodev\Payments\PayPal;
use Puntodev\Payments\PayPalApi;

class PaypalTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('paypal.client_id', 'PAYPAL_ID');
        $app['config']->set('paypal.client_secret', 'PAYPAL_SECRET');
        $app['config']->set('paypal.use_sandbox', 'true');
    }

    #[Test]
    public function default_client()
    {
        /** @var PayPal $paypal */
        $paypal = $this->app->make('paypal');

        $client = $paypal->defaultClient();

        $this->assertInstanceOf(PayPalApi::class, $client);
    }

    #[Test]
    public function with_credentials()
    {
        /** @var PayPal $paypal */
        $paypal = $this->app->make('paypal');

        $client = $paypal->withCredentials('A', 'B');

        $this->assertInstanceOf(PayPalApi::class, $client);
    }
}
