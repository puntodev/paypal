# AGENTS.md

Guía para agentes de IA que trabajen en este repositorio.

## Qué es este proyecto

`puntodev/paypal` es un **paquete de Laravel** (librería de Composer) que provee un
cliente liviano para la **PayPal Orders v2 API**, más verificación de IPN clásico.
No es una aplicación: se publica en Packagist y se consume desde apps Laravel.

- **Namespace:** `Puntodev\Payments\` (PSR-4, mapeado a `src/`)
- **PHP:** `>=8.4 <9.0`
- **Dependencia principal:** `illuminate/support` (`^12.53 || ^13.0`)
- **Licencia:** MIT

## Arquitectura

El paquete se estructura en torno a dos interfaces y sus implementaciones, más un
builder para construir el payload de la orden.

| Archivo | Rol |
|---------|-----|
| `src/PayPal.php` | Interfaz fábrica. Expone `defaultClient()` y `withCredentials($id, $secret)`. |
| `src/PayPalClient.php` | Implementación de `PayPal`. Conserva credenciales por defecto + flag `useSandbox` y crea instancias de `PayPalApiClient`. |
| `src/PayPalApi.php` | Interfaz del cliente HTTP: `createOrder`, `findOrderById`, `captureOrder`, `verifyIpn`. |
| `src/PayPalApiClient.php` | Implementación real contra la API de PayPal usando el HTTP client de Laravel (`Http`). Maneja host sandbox/producción, token OAuth2 cacheado e IPN. |
| `src/OrderBuilder.php` | Builder fluido que arma el array de la orden v2 (`intent`, `purchase_units`, `payment_source`, etc.). |
| `src/PayPalServiceProvider.php` | Registra `PayPal` como singleton, publica el config y aplica `mergeConfigFrom`. |
| `src/PayPalFacade.php` | Facade `Paypal` que resuelve al binding `PayPal::class`. |
| `config/paypal.php` | Lee `PAYPAL_API_CLIENT_ID`, `PAYPAL_API_CLIENT_SECRET`, `SANDBOX_GATEWAYS`. |

### Detalles importantes de comportamiento

- **Sandbox vs producción:** controlado por el flag `useSandbox`. Cambia el host
  (`api.sandbox.paypal.com` vs `api.paypal.com`) y la URL de IPN
  (`ipnpb.sandbox.paypal.com` vs `ipnpb.paypal.com`). El config lo toma de la env
  `SANDBOX_GATEWAYS`.
- **Token OAuth2:** `getToken()` cachea el `access_token` por 1000 segundos con clave
  `paypal-token-{clientId}` vía `Cache::remember`. Usa `withBasicAuth` contra
  `/v1/oauth2/token` con `grant_type=client_credentials`.
- **Órdenes:** todas las llamadas a `/v2/checkout/orders` envían el header
  `Prefer: return=representation` y usan `->throw()`, por lo que los errores HTTP
  se propagan como `RequestException`.
- **IPN:** `verifyIpn()` reenvía el querystring con `cmd=_notify-validate` y devuelve
  el body crudo de PayPal (`VERIFIED` / `INVALID`), sin redirecciones.
- **OrderBuilder:** intent fijo `CAPTURE`, ítems como `DIGITAL_GOODS`,
  `shipping_preference: NO_SHIPPING`. El `discount` solo se agrega al `breakdown`
  cuando es mayor a 0. Locale por defecto `es-AR`.

## Auto-registro en Laravel (package discovery)

Definido en `composer.json` → `extra.laravel`:
- Provider: `Puntodev\Payments\PayPalServiceProvider`
- Alias/Facade: `Paypal` → `Puntodev\Payments\PayPalFacade`

## Cómo correr y testear

```bash
composer install
composer test            # vendor/bin/phpunit
composer test-coverage   # genera coverage HTML en ./coverage
```

- Los tests usan **Orchestra Testbench** (`tests/TestCase.php` extiende
  `Orchestra\Testbench\TestCase` y registra el service provider).
- `phpunit.xml.dist` fuerza `SANDBOX_GATEWAYS=true`, así que los tests apuntan al
  **sandbox de PayPal**.
- ⚠️ **`tests/PayPalApiTest.php` hace llamadas HTTP reales al sandbox.** Requiere
  credenciales válidas en `PAYPAL_API_CLIENT_ID` / `PAYPAL_API_CLIENT_SECRET`
  (en `.env` localmente, o GitHub Secrets en CI). No son tests unitarios aislados.
- CI: `.github/workflows/php.yml` corre sobre PHP 8.4 en cada push/PR a `master`.

## Convenciones

- Sin framework de estilo configurado; seguir el estilo existente (PSR-12, 4 espacios,
  `.editorconfig`).
- Las interfaces (`PayPal`, `PayPalApi`) son el contrato público: si se agrega un método
  al cliente, agregarlo también a la interfaz y a los tests.
- Los métodos de la API devuelven `array`/`?array` (el `->json()` de Laravel) o `string`
  para IPN; mantener esa convención.

## Reglas de workflow (heredadas de la config global del usuario)

- **No commitear en `master`.** Trabajar siempre en branch o worktree.
- Los PRs se abren siempre como **Draft**.
- Hacer `git pull` antes de empezar para tener la última versión.
