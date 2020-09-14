<?php

namespace Puntodev\Payments;

class PayPal
{
    /** @var string */
    private string $clientId;

    /** @var string */
    private string $clientSecret;

    /** @var bool */
    private bool $useSandbox;

    /**
     * PayPalApiFactory constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param bool $useSandbox
     */
    public function __construct(string $clientId, string $clientSecret, bool $useSandbox)
    {
        $this->useSandbox = $useSandbox;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function defaultClient()
    {
        return new PayPalApi(
            $this->clientId,
            $this->clientSecret,
            $this->useSandbox
        );
    }

    public function withCredentials($clientId, $clientSecret)
    {
        return new PayPalApi(
            $clientId,
            $clientSecret,
            $this->useSandbox
        );
    }
}
