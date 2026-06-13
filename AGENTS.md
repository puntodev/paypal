# AGENTS.md

Guidance for AI agents working in this repository.

## What this project is

`puntodev/paypal` is a **Laravel package** (Composer library) that provides a
lightweight client for the **PayPal Orders v2 API**, plus classic IPN verification.
It is not an application: it is published to Packagist and consumed from Laravel apps.

- **Namespace:** `Puntodev\Payments\` (PSR-4, mapped to `src/`)
- **PHP:** `>=8.4 <9.0`
- **Main dependency:** `illuminate/support` (`^12.53 || ^13.0`)
- **License:** MIT

## Architecture

The package is built around two interfaces and their implementations, plus a builder
that assembles the order payload.

| File | Role |
|------|------|
| `src/PayPal.php` | Factory interface. Exposes `defaultClient()` and `withCredentials($id, $secret)`. |
| `src/PayPalClient.php` | `PayPal` implementation. Holds the default credentials + `useSandbox` flag and creates `PayPalApiClient` instances. |
| `src/PayPalApi.php` | HTTP client interface: `createOrder`, `findOrderById`, `captureOrder`, `verifyIpn`. |
| `src/PayPalApiClient.php` | Real implementation against the PayPal API using Laravel's HTTP client (`Http`). Handles the sandbox/production host, cached OAuth2 token and IPN. |
| `src/OrderBuilder.php` | Fluent builder that assembles the Orders v2 array (`intent`, `purchase_units`, `payment_source`, etc.). |
| `src/PayPalServiceProvider.php` | Registers `PayPal` as a singleton, publishes the config and applies `mergeConfigFrom`. |
| `src/PayPalFacade.php` | `Paypal` facade resolving to the `PayPal::class` binding. |
| `config/paypal.php` | Reads `PAYPAL_API_CLIENT_ID`, `PAYPAL_API_CLIENT_SECRET`, `SANDBOX_GATEWAYS`. |

### Important behavior details

- **Sandbox vs production:** controlled by the `useSandbox` flag. It switches the host
  (`api.sandbox.paypal.com` vs `api.paypal.com`) and the IPN URL
  (`ipnpb.sandbox.paypal.com` vs `ipnpb.paypal.com`). The config reads it from the
  `SANDBOX_GATEWAYS` env var.
- **OAuth2 token:** `getToken()` caches the `access_token` for 1000 seconds under the
  key `paypal-token-{clientId}` via `Cache::remember`. It uses `withBasicAuth` against
  `/v1/oauth2/token` with `grant_type=client_credentials`.
- **Orders:** all calls to `/v2/checkout/orders` send the `Prefer: return=representation`
  header and use `->throw()`, so HTTP errors propagate as `RequestException`.
- **IPN:** `verifyIpn()` re-posts the query string with `cmd=_notify-validate` and returns
  PayPal's raw body (`VERIFIED` / `INVALID`), without following redirects.
- **OrderBuilder:** fixed `CAPTURE` intent, items sent as `DIGITAL_GOODS`,
  `shipping_preference: NO_SHIPPING`. The `discount` is only added to the `breakdown`
  when greater than 0. Default locale is `es-AR`.

## Laravel auto-registration (package discovery)

Defined in `composer.json` → `extra.laravel`:
- Provider: `Puntodev\Payments\PayPalServiceProvider`
- Alias/Facade: `Paypal` → `Puntodev\Payments\PayPalFacade`

## How to run and test

```bash
composer install
composer test            # vendor/bin/phpunit
composer test-coverage   # generates HTML coverage report under ./coverage
composer lint            # vendor/bin/pint --test (style check, no changes)
composer format          # vendor/bin/pint (fix style)
```

- Tests use **Orchestra Testbench** (`tests/TestCase.php` extends
  `Orchestra\Testbench\TestCase` and registers the service provider).
- `phpunit.xml.dist` forces `SANDBOX_GATEWAYS=true`, so tests target the
  **PayPal sandbox**.
- ⚠️ **`tests/PayPalApiTest.php` makes real HTTP calls to the sandbox.** It requires
  valid credentials in `PAYPAL_API_CLIENT_ID` / `PAYPAL_API_CLIENT_SECRET`
  (in `.env` locally, or GitHub Secrets in CI). These are not isolated unit tests.
- CI: `.github/workflows/php.yml` runs on PHP 8.4 on every push/PR to `master`,
  including a Pint code-style check.

## Conventions

- Code style is enforced by **Laravel Pint** (`pint.json`, `laravel` preset). Run
  `composer format` before committing; `composer lint` is what CI runs.
- The interfaces (`PayPal`, `PayPalApi`) are the public contract: when adding a method
  to the client, add it to the interface and to the tests as well.
- API methods return `array`/`?array` (Laravel's `->json()`) or `string` for IPN; keep
  that convention.

## Workflow rules (inherited from the user's global config)

- **Do not commit on `master`.** Always work on a branch or worktree.
- PRs are always opened as **Draft**.
- Run `git pull` before starting to make sure you have the latest version.
