<?php

declare(strict_types=1);

namespace LapostaApi;

use LapostaApi\Api\CampaignApi;
use LapostaApi\Api\FieldApi;
use LapostaApi\Api\ListApi;
use LapostaApi\Api\MemberApi;
use LapostaApi\Api\ReportApi;
use LapostaApi\Api\SegmentApi;
use LapostaApi\Api\WebhookApi;
use LapostaApi\Http\Client;
use LapostaApi\Http\RequestFactory;
use LapostaApi\Http\ResponseFactory;
use LapostaApi\Http\StreamFactory;
use LapostaApi\Http\UriFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Class Laposta
 *
 * Central access point for the Laposta API.
 *
 * This class acts as a facade that handles shared configuration (e.g. API key, HTTP client, request factory)
 * and provides convenient access to specific API classes such as CampaignApi, ListApi, MemberApi, etc.
 *
 * Each API is accessed through a dedicated method.
 */
class Laposta
{
    public const VERSION = '2.0.0'; // Version of this package.

    protected string $apiHost = 'api.laposta.nl';
    protected string $apiVersion = 'v2';
    protected string $apiProtocol = 'https';

    protected string $apiKey;
    protected ClientInterface $httpClient;
    protected RequestFactoryInterface $requestFactory;
    protected ResponseFactoryInterface $responseFactory;
    protected StreamFactoryInterface $streamFactory;
    protected UriFactoryInterface $uriFactory;

    protected array $apiInstances = [];
    protected bool $reuseApiInstances;

    /**
     * Constructor to initialize the Laposta client with required configurations.
     *
     * @param string $apiKey The API key for authentication.
     * @param ?ClientInterface $httpClient Optional: A custom HTTP client implementation.
     * @param ?RequestFactoryInterface $requestFactory Optional: A custom request factory.
     * @param ?ResponseFactoryInterface $responseFactory Optional: A custom response factory.
     * @param ?StreamFactoryInterface $streamFactory Optional: A custom stream factory.
     * @param ?UriFactoryInterface $uriFactory Optional: A custom URI factory.
     * @param bool $reuseApiInstances bool $reuseApiInstances Whether to cache API instances for reuse (default: true).
     */
    public function __construct(
        string $apiKey,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UriFactoryInterface $uriFactory = null,
        bool $reuseApiInstances = true,
    ) {
        $this->apiKey = $apiKey;
        $this->reuseApiInstances = $reuseApiInstances;
        $this->responseFactory = $responseFactory ?? new ResponseFactory();
        $this->streamFactory = $streamFactory ?? new StreamFactory();
        $this->uriFactory = $uriFactory ?? new UriFactory();
        $this->requestFactory = $requestFactory ?? new RequestFactory($this->streamFactory, $this->uriFactory);

        $this->httpClient = $httpClient ?? new Client(
            streamFactory: $this->streamFactory,
            responseFactory: $this->responseFactory,
        );
    }

    /**
     * Retrieve the current API key.
     *
     * @return string|null The current API key.
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Set a new API key.
     *
     * @param string $apiKey The API key to set.
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get the current HTTP client instance.
     *
     * @return ClientInterface The HTTP client instance.
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Get the current request factory instance.
     *
     * @return RequestFactoryInterface The request factory instance.
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * Get the current response factory instance.
     *
     * @return ResponseFactoryInterface The response factory instance.
     */
    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    /**
     * Get the current stream factory instance.
     *
     * @return StreamFactoryInterface The stream factory instance.
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * Get the current URI factory instance.
     *
     * @return UriFactoryInterface The URI factory instance.
     */
    public function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    /**
     * Get the API version.
     *
     * @return string The API version.
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * Get the API path including version.
     *
     * @return string The API base path starting with '/'
     */
    public function getApiBasePath(): string
    {
        return '/' . $this->apiVersion;
    }

    /**
     * Get the full base URL for the Laposta API.
     *
     * @return string The full base URL including protocol, host and version.
     */
    public function getApiBaseUrl(): string
    {
        return $this->apiProtocol . '://' . $this->apiHost . $this->getApiBasePath();
    }

    /**
     * Enable or disable caching of API instances.
     *
     * @param bool $reuseApiInstances True to enable caching, false to disable
     * @return void
     */
    public function setReuseApiInstances(bool $reuseApiInstances): void
    {
        $this->reuseApiInstances = $reuseApiInstances;

        // If caching is disabled, remove all instances
        if (!$reuseApiInstances) {
            $this->clearApiInstances();
        }
    }

    /**
     * Removes all cached API instances.
     *
     * @return void
     */
    public function clearApiInstances(): void
    {
        $this->apiInstances = [];
    }

    /**
     * Helper method to retrieve or create an API instance, with caching.
     *
     * @template T
     * @param class-string<T> $className The class name of the API instance
     * @return T The API instance
     */
    protected function getApiInstance(string $className)
    {
        return $this->reuseApiInstances
            ? $this->apiInstances[$className] ??= new $className($this)
            : new $className($this);
    }

    /**
     * Get an instance of the Campaign API class.
     *
     * @return CampaignApi The Campaign API instance.
     */
    public function campaignApi(): CampaignApi
    {
        return $this->getApiInstance(CampaignApi::class);
    }

    /**
     * Get an instance of the Field API class.
     *
     * @return FieldApi The Field API instance.
     */
    public function fieldApi(): FieldApi
    {
        return $this->getApiInstance(FieldApi::class);
    }

    /**
     * Get an instance of the List API class.
     *
     * @return ListApi The List API instance.
     */
    public function listApi(): ListApi
    {
        return $this->getApiInstance(ListApi::class);
    }

    /**
     * Get an instance of the Member API class.
     *
     * @return MemberApi The Member API instance.
     */
    public function memberApi(): MemberApi
    {
        return $this->getApiInstance(MemberApi::class);
    }

    /**
     * Get an instance of the Report API class.
     *
     * @return ReportApi The Report API instance.
     */
    public function reportApi(): ReportApi
    {
        return $this->getApiInstance(ReportApi::class);
    }

    /**
     * Get an instance of the Segment API class.
     *
     * @return SegmentApi The Report API instance.
     */
    public function segmentApi(): SegmentApi
    {
        return $this->getApiInstance(SegmentApi::class);
    }

    /**
     * Get an instance of the Webhook API class.
     *
     * @return WebhookApi The Webhook API instance.
     */
    public function webhookApi(): WebhookApi
    {
        return $this->getApiInstance(WebhookApi::class);
    }
}
