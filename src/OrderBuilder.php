<?php

namespace Puntodev\Payments;

class OrderBuilder
{
    private string $externalId = '';

    private string $currency = '';

    private float $amount = 0;

    private float $discount = 0;

    private string $description = '';

    private string $brandName = '';

    private string $locale = 'es-AR';

    private string $returnUrl = '';

    private string $cancelUrl = '';

    /**
     * OrderBuilder constructor.
     */
    public function __construct() {}

    public function externalId(string $externalId): OrderBuilder
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function currency(string $currency): OrderBuilder
    {
        $this->currency = $currency;

        return $this;
    }

    public function amount(float $amount): OrderBuilder
    {
        $this->amount = $amount;

        return $this;
    }

    public function discount(float $discount): OrderBuilder
    {
        $this->discount = $discount;

        return $this;
    }

    public function description(string $description): OrderBuilder
    {
        $this->description = $description;

        return $this;
    }

    public function brandName(string $brandName): OrderBuilder
    {
        $this->brandName = $brandName;

        return $this;
    }

    public function locale(string $locale): OrderBuilder
    {
        $this->locale = $locale;

        return $this;
    }

    public function returnUrl(string $returnUrl): OrderBuilder
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    public function cancelUrl(string $cancelUrl): OrderBuilder
    {
        $this->cancelUrl = $cancelUrl;

        return $this;
    }

    public function make(): array
    {
        $arr = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'custom_id' => $this->externalId,
                    'description' => $this->description,
                    'amount' => [
                        'currency_code' => $this->currency,
                        'value' => round($this->amount - $this->discount, 2),
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $this->currency,
                                'value' => round($this->amount, 2),
                            ],
                        ],
                    ],
                    'items' => [
                        [
                            'name' => $this->description,
                            'quantity' => '1',
                            'custom_id' => $this->externalId,
                            'unit_amount' => [
                                'currency_code' => $this->currency,
                                'value' => round($this->amount, 2),
                            ],
                            'category' => 'DIGITAL_GOODS',
                        ],
                    ],
                    'payment_options' => [
                        'allowed_payment_method' => 'INSTANT_FUNDING_SOURCE',
                    ],
                ],
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'brand_name' => $this->brandName,
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action' => 'PAY_NOW',
                        'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        'locale' => $this->locale,
                        'return_url' => $this->returnUrl,
                        'cancel_url' => $this->cancelUrl,
                    ],
                ],
            ],
        ];

        if ($this->discount > 0) {
            $arr['purchase_units'][0]['amount']['breakdown']['discount'] = [
                'currency_code' => $this->currency,
                'value' => round($this->discount, 2),
            ];
        }

        return $arr;
    }
}
