<?php

/**
 * Example: Using Laposta with Guzzle and full PSR-17/18 injection.
 *
 * This script demonstrates how to use Guzzle as a PSR-18 HTTP client for the Laposta client,
 * with PSR-17 factory implementations provided by Guzzle itself. It also shows how to use
 * Guzzle middleware to add request/response logging and automatic retries for transient errors.
 *
 * This example is useful if you want full control over HTTP behavior.
 *
 * Note: This example assumes you have required at least the following package:
 * - guzzlehttp/guzzle
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../load-config.php';

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use LapostaApi\Exception\ApiException;
use LapostaApi\Laposta;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// Set variables from environment
$apiKey = getenv('LP_EX_API_KEY');
$listId = getenv('LP_EX_LIST_ID');

if (!$listId) {
    echo "Error: LP_EX_LIST_ID environment variable is not set.\n";
    exit(1);
}

// Initialize the middleware stack.
// HandlerStack::create() sets up Guzzle's default handler
// and allows you to push custom middleware (e.g. for logging, retries).
$stack = HandlerStack::create();

// Set up retry middleware
$stack->push(function (callable $handler) {
    return function (RequestInterface $request, array $options) use ($handler) {
        $maxRetries = 3;

        $retryFn = function (
            RequestInterface $request,
            array $options,
            int $retries = 0,
        ) use (
            $handler,
            &$retryFn,
            $maxRetries
) {
            return $handler($request, $options)->then(
                function (ResponseInterface $response) use (
                    $request,
                    $options,
                    $retries,
                    $retryFn,
                    $maxRetries,
                ) {
                    if (
                        in_array($response->getStatusCode(), [429, 500, 502, 503], true)
                        && $retries < $maxRetries
                    ) {
                        $sleep = $retries + 1;  // linear backoff
                        if ($response->getHeaderLine('Retry-After')) {
                            $sleep = (int)$response->getHeaderLine('Retry-After');
                        }
                        $sleep = max(1, $sleep);
                        echo "Retrying (" . ($retries + 1) . ") "
                            . "after {$response->getStatusCode()}, "
                            . "sleeping for $sleep seconds...\n";
                        sleep($sleep);

                        return $retryFn($request, $options, $retries + 1);
                    }

                    return $response;
                },
            );
        };

        return $retryFn($request, $options);
    };
});

// Set up logging middleware
$stack->push(function (callable $handler) {
    return function (RequestInterface $request, array $options) use ($handler) {
        echo "--- LOG Request ---\n";
        echo $request->getMethod() . ' ' . $request->getUri() . "\n";
        echo $request->getBody() . "\n\n";
        $request->getBody()->rewind();

        return $handler($request, $options)->then(
            function (ResponseInterface $response) {
                echo "--- LOG Response ---\n";
                echo "Status: " . $response->getStatusCode() . "\n";
                echo $response->getBody() . "\n\n";
                return $response;
            },
        );
    };
});

// Set up the Guzzle client
$httpClient = new GuzzleClient(['handler' => $stack]);

// PSR-17 factories
$requestFactory = new HttpFactory();
$responseFactory = new HttpFactory();
$streamFactory = new HttpFactory();
$uriFactory = new HttpFactory();

// Create Laposta instance with dependencies injected
$laposta = new Laposta(
    $apiKey,
    httpClient: $httpClient,
    requestFactory: $requestFactory,
    responseFactory: $responseFactory,
    streamFactory: $streamFactory,
    uriFactory: $uriFactory,
);

// Create a member
$memberApi = $laposta->memberApi();
$memberData = [
    'email' => 'example+' . time() . '@example.com',
    'ip' => '123.123.123.123',
];
try {
    $result = $memberApi->create($listId, $memberData);
    echo "Member created successfully in list_id '$listId':\n";
    print_r($result);
} catch (ApiException $e) {
    echo "ApiException: " . $e->getMessage() . "\n";
    echo "Response body: " . $e->getResponseBody() . "\n";
} catch (ClientException $e) {
    echo "ClientException: " . $e->getMessage() . "\n";
    echo "Response body: " . $e->getResponse()->getBody() . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
