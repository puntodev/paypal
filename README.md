# PayPal API Client for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/puntodev/paypal.svg?style=flat-square)](https://packagist.org/packages/puntodev/paypal)
[![Total Downloads](https://img.shields.io/packagist/dt/puntodev/paypal.svg?style=flat-square)](https://packagist.org/packages/puntodev/paypal)

A lightweight Laravel package that wraps the [PayPal Orders v2 API](https://developer.paypal.com/docs/api/orders/v2/)
(create, find and capture orders) and provides classic IPN verification. It uses
Laravel's HTTP client under the hood and caches the OAuth2 access token automatically.

## Requirements

- PHP `>=8.4 <9.0`
- Laravel 13+ (`illuminate/support` `^13.0`)

## Installation

Install via composer:

```bash
composer require puntodev/paypal
```

The package auto-registers its service provider and the `Paypal` facade via Laravel
package discovery. To publish the config file:

```bash
php artisan vendor:publish --provider="Puntodev\Payments\PayPalServiceProvider" --tag="config"
```

## Configuration

Set the following environment variables:

```dotenv
PAYPAL_API_CLIENT_ID=your-client-id
PAYPAL_API_CLIENT_SECRET=your-client-secret
SANDBOX_GATEWAYS=true   # true -> sandbox, false -> production
```

These map to `config/paypal.php`:

```php
return [
    'client_id' => env('PAYPAL_API_CLIENT_ID'),
    'client_secret' => env('PAYPAL_API_CLIENT_SECRET'),
    'use_sandbox' => env('SANDBOX_GATEWAYS', false),
];
```

When `use_sandbox` is `true` the client targets `api.sandbox.paypal.com` and the
sandbox IPN endpoint; otherwise it targets production.

## Usage

### Resolving the client

Inject the `PayPal` contract (or use the `Paypal` facade) and obtain a `PayPalApi`
instance. Use `defaultClient()` to use the configured credentials, or
`withCredentials()` to override them at runtime (e.g. for multi-tenant setups):

```php
use Puntodev\Payments\PayPal;

public function __construct(private PayPal $paypal) {}

// With the credentials from config/paypal.php
$api = $this->paypal->defaultClient();

// Or with per-request credentials (sandbox flag still comes from config)
$api = $this->paypal->withCredentials($clientId, $clientSecret);
```

### Building an order

`OrderBuilder` produces the payload for the Orders v2 API. The order intent is
`CAPTURE`, items are sent as `DIGITAL_GOODS` with `NO_SHIPPING`, and a discount is
only included when greater than zero:

```php
use Puntodev\Payments\OrderBuilder;

$order = (new OrderBuilder())
    ->externalId('your-internal-id')
    ->currency('USD')
    ->amount(23.20)
    ->discount(2.20)            // optional
    ->description('My custom product')
    ->brandName('My brand name')
    ->locale('es-AR')           // defaults to es-AR
    ->returnUrl('https://example.com/return')
    ->cancelUrl('https://example.com/cancel')
    ->make();
```

### Creating, finding and capturing orders

```php
$created = $api->createOrder($order);
$orderId = $created['id'];

// Send the buyer to the "payer-action" link to approve the payment
$approveUrl = collect($created['links'])
    ->firstWhere('rel', 'payer-action')['href'];

// Later, fetch or capture the order
$order = $api->findOrderById($orderId);
$capture = $api->captureOrder($orderId);
```

All order methods return the decoded JSON response as an `array` and throw
`Illuminate\Http\Client\RequestException` on HTTP errors.

### Verifying IPN notifications

```php
// In your IPN webhook controller
$status = $api->verifyIpn($request->getContent()); // "VERIFIED" or "INVALID"

if ($status === 'VERIFIED') {
    // process the notification
}
```

## Testing

```bash
composer test            # runs PHPUnit
composer test-coverage   # generates HTML coverage report
```

> **Note:** the test suite (`tests/PayPalApiTest.php`) makes **real HTTP calls to
> the PayPal sandbox**. You must provide valid sandbox credentials via
> `PAYPAL_API_CLIENT_ID` and `PAYPAL_API_CLIENT_SECRET`. `phpunit.xml.dist` forces
> `SANDBOX_GATEWAYS=true`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Releasing

Releases are cut from GitHub and the changelog is kept in sync automatically:

1. Merge the pull requests you want to ship into `master`. Label them so the notes
   group nicely (`security`, `enhancement`, `bug`, `dependencies`, `documentation`);
   grouping is configured in [`.github/release.yml`](.github/release.yml).
2. On GitHub, go to **Releases → Draft a new release**, create a `vX.Y.Z` tag
   following [SemVer](https://semver.org/), and click **Generate release notes**.
3. **Publish** the release. Packagist picks up the new tag, and the
   [`update-changelog.yml`](.github/workflows/update-changelog.yml) workflow writes
   the release notes into `CHANGELOG.md` and commits them back to `master`.

The `Unreleased` section in the changelog is just an anchor — release notes flow
from the published GitHub release, so there is no changelog to edit by hand.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email mariano.goldman@puntodev.com.ar
instead of using the issue tracker.

## Credits

- [Mariano Goldman](https://github.com/puntodev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
