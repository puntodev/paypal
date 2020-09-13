<?php


namespace Puntodev\Payments;


use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PayPalApiWrapper
{
    /** @var GuzzleHttpClient */
    private GuzzleHttpClient $client;

    /** @var string */
    private string $apiClientKey;

    /** @var string */
    private string $apiClientSecret;

    /** @var string */
    private string $host;

    /** @var string */
    private string $ipnUrl;

    /**
     * PayPalApiWrapper constructor.
     * @param GuzzleHttpClient $client
     * @param string $apiClientKey
     * @param string $apiClientSecret
     * @param bool $useSandbox
     */
    public function __construct(GuzzleHttpClient $client, string $apiClientKey, string $apiClientSecret, bool $useSandbox)
    {
        $this->client = $client;
        $this->apiClientKey = $apiClientKey;
        $this->apiClientSecret = $apiClientSecret;
        $this->host = $useSandbox ?
            'api.sandbox.paypal.com' :
            'api.paypal.com';
        $this->ipnUrl = $useSandbox ?
            'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' :
            'https://ipnpb.paypal.com/cgi-bin/webscr';
    }

    /**
     * @param array $order
     *
     * @return array
     * @throws GuzzleException
     */
    public function createOrder(array $order): array
    {
        $token = $this->getToken();
        $response = $this->client->post("https://{$this->host}/v2/checkout/orders", [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token['access_token'],
                'Prefer' => 'return=representation',
            ],
            RequestOptions::JSON => $order
        ]);
        return json_decode($response->getBody(), true);
    }

    public function findOrderById(string $id): ?array
    {
        $token = $this->getToken();
        $response = $this->client->get("https://{$this->host}/v2/checkout/orders/$id", [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token['access_token'],
            ],
        ]);
        return json_decode($response->getBody(), true);
    }

    public function captureOrder(string $orderId): ?array
    {
        $token = $this->getToken();
        $response = $this->client->post("https://{$this->host}/v2/checkout/orders/$orderId/capture", [
            RequestOptions::HEADERS => [
                'Content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $token['access_token'],
                'Prefer' => 'return=representation',
            ],
        ]);
        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $querystring
     *
     * @return string
     * @throws GuzzleException
     */
    public function verifyIpn(string $querystring)
    {
        return $this->client->post($this->ipnUrl, [
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => 'PHP-IPN-Verification-Script',
                'Connection' => 'Close',
            ],
            RequestOptions::BODY => 'cmd=_notify-validate&' . $querystring,
        ])
            ->getBody()
            ->getContents();
    }

    private function getToken(): array
    {
        return Cache::remember("paypal-token-{$this->apiClientKey}", 1000, function () {
            Log::debug('Obtaining PayPal token from live server');
            $response = $this->client->post("https://{$this->host}/v1/oauth2/token", [
                RequestOptions::AUTH => [
                    $this->apiClientKey,
                    $this->apiClientSecret,
                    'Basic'
                ],
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                RequestOptions::FORM_PARAMS => [
                    'grant_type' => 'client_credentials',
                ]
            ]);
            return json_decode($response->getBody(), true);
        });
    }
}
