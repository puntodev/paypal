<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Puntodev\Payments\OrderBuilder;

class OrderBuilderTest extends TestCase
{
    #[Test]
    public function create_order_with_int_amount()
    {
        $order = (new OrderBuilder())
            ->externalId('31fe5538-8589-437d-8823-3b0574186a5f')
            ->currency('USD')
            ->amount(23.206)
            ->description('My custom product')
            ->brandName('My brand name')
            ->returnUrl('http://localhost:8080/return')
            ->cancelUrl('http://localhost:8080/cancel')
            ->make();

        $this->assertEquals([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'custom_id' => '31fe5538-8589-437d-8823-3b0574186a5f',
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => 23.21,
                    ],
                    'description' => 'My custom product',
                    'payment_options' => [
                        'allowed_payment_method' => 'INSTANT_FUNDING_SOURCE'
                    ],
                ]
            ],
            'application_context' => [
                'brand_name' => 'My brand name',
                'locale' => 'es-AR',
                'user_action' => 'PAY_NOW',
                'payment_method' => [
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => 'http://localhost:8080/return',
                'cancel_url' => 'http://localhost:8080/cancel'
            ],
        ], $order);
    }
}
