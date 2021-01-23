<?php


namespace Puntodev\Payments;


class OrderBuilder
{
    private string $externalId = '';
    private string $currency = '';
    private float $amount = 0;
    private string $description = '';
    private string $brandName = '';
    private string $locale = 'es-AR';
    private string $returnUrl = '';
    private string $cancelUrl = '';

    /**
     * OrderBuilder constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $externalId
     * @return OrderBuilder
     */
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

    /**
     * @param float $amount
     * @return OrderBuilder
     */
    public function amount(float $amount): OrderBuilder
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string $description
     * @return OrderBuilder
     */
    public function description(string $description): OrderBuilder
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $brandName
     * @return OrderBuilder
     */
    public function brandName(string $brandName): OrderBuilder
    {
        $this->brandName = $brandName;
        return $this;
    }

    /**
     * @param string $locale
     * @return OrderBuilder
     */
    public function locale(string $locale): OrderBuilder
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @param string $returnUrl
     * @return OrderBuilder
     */
    public function returnUrl(string $returnUrl): OrderBuilder
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @param string $cancelUrl
     * @return OrderBuilder
     */
    public function cancelUrl(string $cancelUrl): OrderBuilder
    {
        $this->cancelUrl = $cancelUrl;
        return $this;
    }

    public function make(): array
    {
        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'custom_id' => $this->externalId,
                    'amount' => [
                        'currency_code' => $this->currency,
                        'value' => round($this->amount, 2),
                    ],
                    'description' => $this->description,
                    'payment_options' => [
                        'allowed_payment_method' => 'INSTANT_FUNDING_SOURCE'
                    ],
                ]
            ],
            'application_context' => [
                'brand_name' => $this->brandName,
                'locale' => $this->locale,
                'user_action' => 'PAY_NOW',
                'payment_method' => [
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => $this->returnUrl,
                'cancel_url' => $this->cancelUrl
            ],
        ];
    }
}
