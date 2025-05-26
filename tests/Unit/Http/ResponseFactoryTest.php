<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Http\Response;
use LapostaApi\Http\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseFactoryTest extends TestCase
{
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateResponseWithDefaultParameters(): void
    {
        $response = $this->responseFactory->createResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    public function testCreateResponseWithCustomStatusCodeAndReasonPhrase(): void
    {
        $response = $this->responseFactory->createResponse(978, 'Custom 978');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(978, $response->getStatusCode());
        $this->assertEquals('Custom 978', $response->getReasonPhrase());
    }

    protected function tearDown(): void
    {
        unset($this->responseFactory);
        parent::tearDown();
    }
}
