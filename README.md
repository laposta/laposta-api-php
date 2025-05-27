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

## Manual Installation ##

If you're not using Composer, include the autoloader:

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