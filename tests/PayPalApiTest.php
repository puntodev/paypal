<?php

namespace Tests;

use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Puntodev\Payments\OrderBuilder;
use Puntodev\Payments\PayPalApi;

class PayPalApiTest extends TestCase
{
    use WithFaker;

    private PayPalApi $paypalApi;

    public function setUp(): void
    {
        parent::setUp();
        $this->paypalApi = new PayPalApi(
            config('paypal.client_id'),
            config('paypal.client_secret'),
            config('paypal.use_sandbox'),
        );
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('paypal.client_id', env('PAYPAL_API_CLIENT_ID'));
        $app['config']->set('paypal.client_secret', env('PAYPAL_API_CLIENT_SECRET'));
        $app['config']->set('paypal.use_sandbox', env('SANDBOX_GATEWAYS'));
    }

    #[Test]
    public function verify_ipn()
    {
        $this->assertEquals('INVALID', $this->paypalApi->verifyIpn('saraza'));
    }

    /**
     * @return void
     * @throws RequestException
     */
    #[Test]
    public function create_order()
    {
        $order = (new OrderBuilder())
            ->externalId($this->faker->uuid)
            ->currency('USD')
            ->amount(23.20)
            ->description('My custom product')
            ->brandName('My brand name')
            ->returnUrl('http://localhost:8080/return')
            ->cancelUrl('http://localhost:8080/cancel')
            ->make();

        $createdOrder = $this->paypalApi->createOrder($order);
        Log::debug('Created Order: ', ['createdOrder' => $createdOrder]);

        $this->assertEquals('CREATED', $createdOrder['status']);
        $this->assertCount(4, $createdOrder['links']);
        $link = collect($createdOrder['links'])
            ->filter(function ($link) {
                return $link['method'] === 'GET' && $link['rel'] === 'approve';
            })
            ->first();
        $this->assertStringStartsWith('https://www.sandbox.paypal.com/checkoutnow', $link['href']);
    }


    /**
     * @return void
     * @throws Exception
     */
    #[Test]
    public function find_order_by_id_invalid()
    {
        $this->expectException(RequestException::class);
        $this->paypalApi->findOrderById('invalid-id');
    }

    /**
     * @return void
     * @throws Exception
     */
    #[Test]
    public function find_order_by_id()
    {
        $payment = $this->paypalApi->findOrderById('5KX43952KL513742C');
        $this->assertIsArray($payment);
    }

    /**
     * @return void
     * @throws Exception
     */
    #[Test]
    public function capture_order()
    {
        $this->expectException(RequestException::class);

        $payment = $this->paypalApi->captureOrder('7F690940G8438461U');
        $this->assertIsArray($payment);
    }
}
