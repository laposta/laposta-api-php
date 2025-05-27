<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit;

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
use LapostaApi\Laposta;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class LapostaTest extends TestCase
{
    private const API_KEY = 'test_api_key';
    private const LIST_ID = 'test_list_id';

    /** @var Laposta */
    private $laposta;

    /** @var ClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $httpClientMock;

    /** @var RequestFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $requestFactoryMock;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(ClientInterface::class);
        $this->requestFactoryMock = $this->createMock(RequestFactoryInterface::class);

        $this->laposta = new Laposta(
            apiKey: self::API_KEY,
            httpClient: $this->httpClientMock,
            requestFactory: $this->requestFactoryMock
        );
    }

    public function testConstructorWithDefaults(): void
    {
        $laposta = new Laposta(self::API_KEY);

        $this->assertEquals(
            self::API_KEY,
            $laposta->getApiKey(),
            'API key not set correctly in constructor'
        );
        $this->assertInstanceOf(
            RequestFactory::class,
            $laposta->getRequestFactory(),
            'Request factory not initialized correctly'
        );
        $this->assertInstanceOf(
            Client::class,
            $laposta->getHttpClient(),
            'HTTP client not initialized correctly'
        );
        $this->assertInstanceOf(
            ResponseFactory::class,
            $laposta->getResponseFactory(),
            'Response factory not initialized correctly'
        );
        $this->assertInstanceOf(
            StreamFactory::class,
            $laposta->getStreamFactory(),
            'Stream factory not initialized correctly'
        );
        $this->assertInstanceOf(
            UriFactory::class,
            $laposta->getUriFactory(),
            'URI factory not initialized correctly'
        );
    }

    public function testAllConstructorPropertiesAreCorrectlyRetrievedByGetters(): void
    {
        // Arrange
        $apiKey = 'test_constructor_api_key';
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $uriFactory = $this->createMock(UriFactoryInterface::class);

        // Act
        $laposta = new Laposta(
            apiKey: $apiKey,
            httpClient: $httpClient,
            requestFactory: $requestFactory,
            responseFactory: $responseFactory,
            streamFactory: $streamFactory,
            uriFactory: $uriFactory
        );

        // Assert
        $this->assertSame(
            $apiKey,
            $laposta->getApiKey(),
            'getApiKey() did not return the correct value'
        );
        $this->assertSame(
            $httpClient,
            $laposta->getHttpClient(),
            'getHttpClient() did not return the correct object'
        );
        $this->assertSame(
            $requestFactory,
            $laposta->getRequestFactory(),
            'getRequestFactory() did not return the correct object'
        );
        $this->assertSame(
            $responseFactory,
            $laposta->getResponseFactory(),
            'getResponseFactory() did not return the correct object'
        );
        $this->assertSame(
            $streamFactory,
            $laposta->getStreamFactory(),
            'getStreamFactory() did not return the correct object'
        );
        $this->assertSame(
            $uriFactory,
            $laposta->getUriFactory(),
            'getUriFactory() did not return the correct object'
        );
    }

    public function testConstructorAppliesConfiguration(): void
    {
        $this->assertEquals(
            self::API_KEY,
            $this->laposta->getApiKey(),
            'API key not configured correctly'
        );

        $this->assertSame(
            $this->requestFactoryMock,
            $this->laposta->getRequestFactory(),
            'Request factory not configured correctly'
        );
        $this->assertSame(
            $this->httpClientMock,
            $this->laposta->getHttpClient(),
            'HTTP client not configured correctly'
        );
    }

    public function testApiKeyCanBeChanged(): void
    {
        $newApiKey = 'new_api_key';
        $this->laposta->setApiKey($newApiKey);

        $this->assertEquals(
            $newApiKey,
            $this->laposta->getApiKey(),
            'API key not updated correctly'
        );
    }

    public function testProvidesCorrectApiInstances(): void
    {
        $this->assertInstanceOf(
            CampaignApi::class,
            $this->laposta->campaignApi(),
            'Campaign API instance not initialized correctly'
        );
        $this->assertInstanceOf(
            ListApi::class,
            $this->laposta->listApi(),
            'List API instance not initialized correctly'
        );
        $this->assertInstanceOf(
            ReportApi::class,
            $this->laposta->reportApi(),
            'Report API instance not initialized correctly'
        );
        $this->assertInstanceOf(
            FieldApi::class,
            $this->laposta->fieldApi(),
            'Field API instance not initialized correctly'
        );
        $this->assertInstanceOf(
            MemberApi::class,
            $this->laposta->memberApi(),
            'Member API instance not initialized correctly'
        );
        $this->assertInstanceOf(
            WebhookApi::class,
            $this->laposta->webhookApi(),
            'Webhook API instance not initialized correctly'
        );
        $this->assertInstanceOf(
            SegmentApi::class,
            $this->laposta->segmentApi(),
            'Segment API instance not initialized correctly'
        );
    }

    public function testFormsCorrectApiBaseUrl(): void
    {
        $expectedUrl = 'https://api.laposta.nl/' . $this->laposta->getApiVersion();
        $this->assertEquals(
            $expectedUrl,
            $this->laposta->getApiBaseUrl(),
            'API base URL not formed correctly'
        );
    }

    public function testApiInstancesAreReusedByDefault(): void
    {
        $campaignApi1 = $this->laposta->campaignApi();
        $campaignApi2 = $this->laposta->campaignApi();
        $listApi1 = $this->laposta->listApi();
        $listApi2 = $this->laposta->listApi();
        $memberApi1 = $this->laposta->memberApi();
        $memberApi2 = $this->laposta->memberApi();

        $this->assertSame(
            $campaignApi1,
            $campaignApi2,
            'Campaign API instances not reused when reuse is enabled'
        );
        $this->assertSame(
            $listApi1,
            $listApi2,
            'List API instances not reused when reuse is enabled'
        );
        $this->assertSame(
            $memberApi1,
            $memberApi2,
            'Member API instances not reused when reuse is enabled'
        );
    }

    public function testApiInstancesAreNotReusedWhenDisabled(): void
    {
        $laposta = new Laposta(
            apiKey: self::API_KEY,
            httpClient: $this->httpClientMock,
            requestFactory: $this->requestFactoryMock,
            reuseApiInstances: false
        );

        $campaignApi1 = $laposta->campaignApi();
        $campaignApi2 = $laposta->campaignApi();

        $this->assertNotSame(
            $campaignApi1,
            $campaignApi2,
            'API instances are being reused when reuse is disabled'
        );
    }

    public function testSetReuseApiInstancesChangesReuseBehavior(): void
    {
        $campaignApi1 = $this->laposta->campaignApi();
        $campaignApi2 = $this->laposta->campaignApi();
        $this->assertSame(
            $campaignApi1,
            $campaignApi2,
            'API instances are not being reused with default settings'
        );

        $this->laposta->setReuseApiInstances(false);

        $campaignApi3 = $this->laposta->campaignApi();
        $campaignApi4 = $this->laposta->campaignApi();

        $this->assertNotSame(
            $campaignApi2,
            $campaignApi3,
            'API instances are being reused after disabling reuse'
        );
        $this->assertNotSame(
            $campaignApi3,
            $campaignApi4,
            'API instances are being reused after disabling reuse'
        );

        $this->laposta->setReuseApiInstances(true);

        $campaignApi5 = $this->laposta->campaignApi();
        $campaignApi6 = $this->laposta->campaignApi();

        $this->assertSame(
            $campaignApi5,
            $campaignApi6,
            'API instances are not being reused after re-enabling reuse'
        );
        $this->assertNotSame(
            $campaignApi4,
            $campaignApi5,
            'Previous API instances were not cleared when re-enabling reuse'
        );
    }

    public function testClearApiInstancesRemovesAllCachedInstances(): void
    {
        $campaignApi1 = $this->laposta->campaignApi();
        $listApi1 = $this->laposta->listApi();

        $this->laposta->clearApiInstances();

        $campaignApi2 = $this->laposta->campaignApi();
        $listApi2 = $this->laposta->listApi();

        $this->assertNotSame(
            $campaignApi1,
            $campaignApi2,
            'Cached campaign API instance not cleared properly'
        );
        $this->assertNotSame(
            $listApi1,
            $listApi2,
            'Cached list API instance not cleared properly'
        );
    }
}
