<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Http\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseTest extends TestCase
{
    public function testDefaultResponse(): void
    {
        $response = new Response();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    public function testCustomStatusCodeAndReasonPhrase(): void
    {
        $response = new Response(404, 'Custom Not Found');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Custom Not Found', $response->getReasonPhrase());
    }

    public function testStandardReasonPhraseForKnownStatusCodes(): void
    {
        $response = new Response(403);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Forbidden', $response->getReasonPhrase());
    }

    public function testUnknownStatusCodeReasonPhrase(): void
    {
        $response = new Response(999);

        $this->assertEquals(999, $response->getStatusCode());
        $this->assertEquals('', $response->getReasonPhrase()); // Default for unknown codes
    }

    public function testWithStatusReturnsNewInstance(): void
    {
        $response = new Response(200);
        $newResponse = $response->withStatus(201, 'Created');

        $this->assertNotSame($response, $newResponse); // Ensure immutability
        $this->assertEquals(201, $newResponse->getStatusCode());
        $this->assertEquals('Created', $newResponse->getReasonPhrase());

        // Original response should remain unchanged
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    /**
     * @covers \LapostaApi\Http\Response::getDefaultReasonPhrase
     */
    public function testWithStatusDefaultsToStandardReasonPhrase(): void
    {
        $response = new Response(200);
        $newResponse = $response->withStatus(500);

        $this->assertEquals(500, $newResponse->getStatusCode());
        $this->assertEquals('Internal Server Error', $newResponse->getReasonPhrase());
    }
}
