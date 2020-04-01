# Installation

To get started, add the following to your PHP script:

require_once("/path/to/laposta-php/lib/Laposta.php");

## Simple usage looks like:

Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

$member = new Laposta_Member("BaImMu3JZA");

$result = $member->get("maartje@example.net");

## HTTP(S)

Default is HTTPS. If you can't use HTTPS, do this:

Laposta::setHttps(false);

If you're getting errors about certificate problems, you do this:

Laposta::setHttpsDisableVerifyPeer(true);

Note: this gets rid of the errors, but can introduce security issues that SSL is designed to protect against. A better solution is to install the CA's certs: https://stackoverflow.com/questions/6400300

# Documentation

Please see https://api.laposta.nl/doc for up-to-date documentation.
