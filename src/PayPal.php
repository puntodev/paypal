<?php

namespace Puntodev\Payments;

use GuzzleHttp\Client as GuzzleHttpClient;

class PayPal
{
    /** @var GuzzleHttpClient */
    private GuzzleHttpClient $client;

    /** @var string */
    private string $clientId;

    /** @var string */
    private string $clientSecret;

    /** @var bool */
    private bool $useSandbox;

    /**
     * PayPalApiFactory constructor.
     *
     * @param GuzzleHttpClient $client
     * @param string $clientId
     * @param string $clientSecret
     * @param bool $useSandbox
     */
    public function __construct(GuzzleHttpClient $client, string $clientId, string $clientSecret, bool $useSandbox)
    {
        $this->useSandbox = $useSandbox;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->client = $client;
    }

    public function defaultClient()
    {
        return new PayPalApiWrapper(
            $this->client,
            $this->clientId,
            $this->clientSecret,
            $this->useSandbox
        );
    }

    public function withCredentials($clientId, $clientSecret)
    {
        return new PayPalApiWrapper(
            $this->client,
            $clientId,
            $clientSecret,
            $this->useSandbox
        );
    }
}
