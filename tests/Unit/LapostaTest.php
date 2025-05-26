<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit;

use LapostaApi\Api\CampaignApi;
use LapostaApi\Api\FieldApi;
use LapostaApi\Api\ListApi;
use LapostaApi\Api\MemberApi;
use LapostaApi\Api\ReportApi;
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
            self::API_KEY,
            $this->httpClientMock,
            $this->requestFactoryMock,
        );
    }

    public function testConstructorWithDefaults(): void
    {
        $laposta = new Laposta(self::API_KEY);

        $this->assertEquals(self::API_KEY, $laposta->getApiKey());
        $this->assertInstanceOf(RequestFactory::class, $laposta->getRequestFactory());
        $this->assertInstanceOf(Client::class, $laposta->getHttpClient());
        $this->assertInstanceOf(ResponseFactory::class, $laposta->getResponseFactory());
        $this->assertInstanceOf(StreamFactory::class, $laposta->getStreamFactory());
        $this->assertInstanceOf(UriFactory::class, $laposta->getUriFactory());
    }

    public function testAllConstructorPropertiesAreCorrectlyRetrievedByGetters(): void
    {
        // Arrange - Create new instances for a clean test
        $apiKey = 'test_constructor_api_key';
        $httpClient = $this->createMock(ClientInterface::class);
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $uriFactory = $this->createMock(UriFactoryInterface::class);

        // Act - Create new instance with all parameters
        $laposta = new Laposta(
            $apiKey,
            $httpClient,
            $requestFactory,
            $responseFactory,
            $streamFactory,
            $uriFactory,
        );

        // Assert - Verify getters return the correct values
        $this->assertSame($apiKey, $laposta->getApiKey(), 'getApiKey() did not return the correct value');
        $this->assertSame($httpClient, $laposta->getHttpClient(), 'getHttpClient() did not return the correct object');
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
        $this->assertSame($uriFactory, $laposta->getUriFactory(), 'getUriFactory() did not return the correct object');
    }

    /**
     * Test that the constructor applies the correct configuration
     */
    public function testConstructorAppliesConfiguration(): void
    {
        // Test that the API key is correctly configured
        $this->assertEquals(self::API_KEY, $this->laposta->getApiKey());

        // Test that the injected dependencies are correctly configured
        $this->assertSame($this->requestFactoryMock, $this->laposta->getRequestFactory());
        $this->assertSame($this->httpClientMock, $this->laposta->getHttpClient());
    }

    /**
     * Test that we can change the API key after instantiation
     */
    public function testApiKeyCanBeChanged(): void
    {
        $newApiKey = 'new_api_key';
        $this->laposta->setApiKey($newApiKey);

        $this->assertEquals($newApiKey, $this->laposta->getApiKey());
    }

    /**
     * Test that the APIs are correctly initialized and accessible
     */
    public function testProvidesCorrectApiInstances(): void
    {
        $this->assertInstanceOf(CampaignApi::class, $this->laposta->campaignApi());
        $this->assertInstanceOf(ListApi::class, $this->laposta->listApi());
        $this->assertInstanceOf(ReportApi::class, $this->laposta->reportApi());
        $this->assertInstanceOf(FieldApi::class, $this->laposta->fieldApi(self::LIST_ID));
        $this->assertInstanceOf(MemberApi::class, $this->laposta->memberApi(self::LIST_ID));
        $this->assertInstanceOf(WebhookApi::class, $this->laposta->webhookApi(self::LIST_ID));
    }

    public function testFormsCorrectApiBaseUrl(): void
    {
        $expectedUrl = 'https://api.laposta.nl/' . $this->laposta->getApiVersion();
        $this->assertEquals($expectedUrl, $this->laposta->getApiBaseUrl());
    }
}
