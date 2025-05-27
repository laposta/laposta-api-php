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
        $response = new Response(401);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('Unauthorized', $response->getReasonPhrase());
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

    public function testWithStatusDefaultsToStandardReasonPhrase(): void
    {
        $response = new Response();

        // Test a range of known HTTP status codes
        $knownCodes = [200, 201, 400, 401, 402, 404, 429, 500];

        foreach ($knownCodes as $code) {
            $newResponse = $response->withStatus($code);
            $this->assertEquals($code, $newResponse->getStatusCode());
            $this->assertNotEmpty($newResponse->getReasonPhrase());
            $this->assertIsString($newResponse->getReasonPhrase());
        }

        // Test unknown code
        $unknownResponse = $response->withStatus(999);
        $this->assertEquals(999, $unknownResponse->getStatusCode());
        $this->assertEquals('', $unknownResponse->getReasonPhrase());
    }
}
