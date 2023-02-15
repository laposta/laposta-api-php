## Requirements ##
To use the Laposta API, the following things are required:
+ PHP >= 5.6
+ CURL PHP extension
+ JSON PHP extension

## Composer Installation ##

The easiest way to install this library is to require it with [Composer](http://getcomposer.org/doc/00-intro.md).

    $ composer require laposta/laposta-api-php

# Manual Installation

If you're not using Composer, add the following to your PHP script:
```php
require_once("/path/to/laposta-php/lib/Laposta.php");
```

## Simple usage looks like:
```php
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");
$member = new Laposta_Member("BaImMu3JZA");
$result = $member->get("member@example.net");
 ```

## HTTP(S)
Default is HTTPS. If you can't use HTTPS, do this:
```php
Laposta::setHttps(false);
```

If you're getting errors about certificate problems, you do this:
```php
Laposta::setHttpsDisableVerifyPeer(true);
```
Note: this gets rid of the errors, but can introduce security issues that SSL is designed to protect against. A better solution is to install the CA's certs: https://stackoverflow.com/questions/6400300

# Documentation
Please see https://api.laposta.nl/doc for up-to-date documentation.