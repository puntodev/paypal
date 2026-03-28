<?php

namespace Puntodev\Payments;

class PayPalClient implements PayPal
{
    private string $clientId;
    private string $clientSecret;
    private bool $useSandbox;

    public function __construct(string $clientId, string $clientSecret, bool $useSandbox)
    {
        $this->useSandbox = $useSandbox;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function defaultClient(): PayPalApi
    {
        return new PayPalApiClient(
            $this->clientId,
            $this->clientSecret,
            $this->useSandbox
        );
    }

    public function withCredentials(string $clientId, string $clientSecret): PayPalApi
    {
        return new PayPalApiClient(
            $clientId,
            $clientSecret,
            $this->useSandbox
        );
    }
}
