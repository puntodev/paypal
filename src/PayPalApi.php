<?php


namespace Puntodev\Payments;


use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalApi
{
    /** @var string */
    private string $apiClientKey;

    /** @var string */
    private string $apiClientSecret;

    /** @var string */
    private string $host;

    /** @var string */
    private string $ipnUrl;

    /**
     * PayPalApi constructor.
     * @param string $apiClientKey
     * @param string $apiClientSecret
     * @param bool $useSandbox
     */
    public function __construct(string $apiClientKey, string $apiClientSecret, bool $useSandbox)
    {
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
     * @return array
     * @throws RequestException
     */
    public function createOrder(array $order): array
    {
        $token = $this->getToken();
        return Http::withToken($token['access_token'])
            ->withHeaders([
                'Prefer' => 'return=representation',
            ])
            ->post("https://{$this->host}/v2/checkout/orders", $order)
            ->throw()
            ->json();
    }

    /**
     * @param string $id
     * @return array|null
     * @throws RequestException
     */
    public function findOrderById(string $id): ?array
    {
        $token = $this->getToken();
        return Http::withToken($token['access_token'])
            ->get("https://{$this->host}/v2/checkout/orders/$id")
            ->throw()
            ->json();
    }

    /**
     * @param string $orderId
     * @return array|null
     * @throws RequestException
     */
    public function captureOrder(string $orderId): ?array
    {
        $token = $this->getToken();
        return Http::withToken($token['access_token'])
            ->withHeaders([
                'Prefer' => 'return=representation',
            ])
            ->withBody(null, 'application/json')
            ->post("https://{$this->host}/v2/checkout/orders/$orderId/capture", [])
            ->throw()
            ->json();
    }

    /**
     * @param string $querystring
     * @return string
     * @throws RequestException
     */
    public function verifyIpn(string $querystring)
    {
        return Http::withHeaders([
            'User-Agent' => 'PHP-IPN-Verification-Script',
            'Connection' => 'Close',
        ])
            ->withoutRedirecting()
            ->withBody('cmd=_notify-validate&' . $querystring, 'application/x-www-form-urlencoded')
            ->post($this->ipnUrl)
            ->throw()
            ->body();
    }

    /**
     * @return array
     */
    private function getToken(): array
    {
        return Cache::remember("paypal-token-{$this->apiClientKey}", 1000, function () {
            Log::debug('Obtaining PayPal token from live server');
            return Http::withBasicAuth($this->apiClientKey, $this->apiClientSecret)
                ->asForm()
                ->post("https://{$this->host}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ])
                ->throw()
                ->json();
        });
    }
}
