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

## Manual Installation (Version-Scoped, Recommended) ##

This is the recommended manual installation path for WordPress and other plugin ecosystems. Multiple plugins may
bundle different PSR-7 versions and different releases of `laposta-api-php` in the same runtime. The version-scoped
build avoids both kinds of collisions by prefixing vendor dependencies and rewriting the public namespace to a
release-specific value.

1. Download the version-scoped zip:
   - Latest release (direct download): https://github.com/laposta/laposta-api-php/releases/latest/download/laposta-api-version-scoped.zip
   - Specific version: https://github.com/laposta/laposta-api-php/releases/download/X.Y.Z/laposta-api-version-scoped.zip
2. Extract it into your plugin (or another shared location).
3. Load the version-scoped autoloader:

```php
require_once __DIR__ . '/laposta-api-version-scoped/autoload.php';
```

The namespace suffix is derived from the release semantic version by concatenating major, minor, and patch.
For example, release `2.3.0` exposes `LapostaApi230\Laposta`.

```php
$laposta = new LapostaApi230\Laposta('your_api_key');
```

This build prefixes vendor dependencies under `LapostaApi230\Vendor\*`, so no global `Psr\*` symbols are introduced.
The version-scoped build is intended for the default HTTP client; if you need to inject your own PSR-18/17/7
implementations, use the Composer distribution instead.

## Manual Installation (Scoped, Compatibility Option) ##

This distribution only scopes vendor dependencies and keeps the public `LapostaApi\*` namespace unchanged.
It is mainly useful if you need a stable namespace for compatibility and you know only one Laposta library version
will be loaded in the runtime.

1. Download the scoped zip:
   - Latest release (direct download): https://github.com/laposta/laposta-api-php/releases/latest/download/laposta-api-scoped.zip
   - Specific version: https://github.com/laposta/laposta-api-php/releases/download/X.Y.Z/laposta-api-scoped.zip
2. Extract it into your plugin (or another shared location).
3. Load the scoped autoloader:

```php
require_once __DIR__ . '/laposta-api-scoped/autoload.php';
```

This build prefixes vendor dependencies under `LapostaApi\Vendor\*`, but it does not isolate the public
`LapostaApi\*` namespace across plugin versions.

## Manual Installation (Unscoped, Not Recommended) ##

This path should only be used if you fully control the runtime and do not have other plugins/libraries that might
define `Psr\*` symbols. In WordPress and other plugin ecosystems, use the version-scoped build above.

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
