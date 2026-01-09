# Laposta API PHP

[![Build](https://github.com/laposta/laposta-api-php/actions/workflows/tests.yml/badge.svg)](https://github.com/laposta/laposta-api-php/actions)
[![Coverage](https://codecov.io/gh/laposta/laposta-api-php/branch/main/graph/badge.svg)](https://codecov.io/gh/laposta/laposta-api-php)
[![Packagist Version](https://img.shields.io/packagist/v/laposta/laposta-api-php)](https://packagist.org/packages/laposta/laposta-api-php)
[![PHP Version](https://img.shields.io/packagist/php-v/laposta/laposta-api-php)](https://packagist.org/packages/laposta/laposta-api-php)
[![License](https://img.shields.io/github/license/laposta/laposta-api-php)](https://github.com/laposta/laposta-api-php/blob/main/LICENSE)

A PHP library for interacting with the Laposta API, compatible with PSR-18 and PSR-17 standards.

## Requirements ##

To use the Laposta API, the following is required:

+ PHP >= 8.1
+ cURL PHP extension
+ JSON PHP extension

## Composer Installation ##

The easiest way to install this library is by requiring it via [Composer](https://getcomposer.org/doc/00-intro.md):

```bash
composer require laposta/laposta-api-php
```

## Manual Installation (Scoped, Recommended) ##

This is the recommended manual installation path. In WordPress and other plugin ecosystems, multiple plugins may
bundle different PSR-7 versions under the same `Psr\` namespace. The scoped build avoids fatal interface signature
conflicts by prefixing vendor dependencies.

1. Download the scoped zip:
   - Latest release (look under Assets): https://github.com/laposta/laposta-api-php/releases/latest
   - Specific version: https://github.com/laposta/laposta-api-php/releases/download/X.Y.Z/laposta-api-scoped-X.Y.Z.zip
2. Extract it into your plugin (or another shared location).
3. Load the scoped autoloader:

```php
require_once __DIR__ . '/laposta-api-scoped/autoload.php';
```

This build prefixes all vendor dependencies under `LapostaApi\Vendor\*`, so no global `Psr\*` symbols are introduced.
The scoped build is intended for the default HTTP client; if you need to inject your own PSR-18/17/7
implementations, use the Composer distribution instead.

## Manual Installation (Unscoped, Not Recommended) ##

This path should only be used if you fully control the runtime and do not have other plugins/libraries that might
define `Psr\*` symbols. In WordPress and other plugin ecosystems, use the scoped build above.

To use the unscoped bundle, include the autoloader:

```php
require_once("/path/to/laposta-api-php/standalone/autoload.php");
```

## Quick Example ##

```php
$laposta = new LapostaApi\Laposta('your_api_key');
$member = $laposta->memberApi()->create($listId, ['email' => 'test@example.com', 'ip' => '123.123.123.123']);
```

## Examples

This project includes a set of real, runnable examples organized by API resource (e.g., list, campaign, member).  
Each example demonstrates a specific API operation and can be run via PHP CLI.  
See [examples/README.md](examples/README.md) for setup instructions and an overview of the available examples.

## Extensibility ##

This library is built around PHP standards (PSR-18/17) and is designed to be flexible.  
You can inject your own HTTP client and factories (e.g. Guzzle, Nyholm, Symfony components) via the constructor:

```php
$laposta = new LapostaApi\Laposta(
    'your_api_key',
    httpClient: new \GuzzleHttp\Client(), // implements PSR-18
    requestFactory: ...,
    responseFactory: ...,
    streamFactory: ...,
    uriFactory: ...
);
```

If no client or factories are provided, the library uses its own lightweight implementations by default.

## API Documentation ##

For the full API reference, see [https://api.laposta.nl/doc](https://api.laposta.nl/doc).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a complete list of changes.

## License ##

This library is open-sourced software licensed under the [MIT license](LICENSE).
