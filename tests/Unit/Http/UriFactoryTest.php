<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Http\Uri;
use LapostaApi\Http\UriFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class UriFactoryTest extends TestCase
{
    private UriFactory $uriFactory;

    protected function setUp(): void
    {
        $this->uriFactory = new UriFactory();
    }

    public function testCreateUriWithEmptyString(): void
    {
        $uri = $this->uriFactory->createUri();

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEquals('', (string)$uri);
    }

    public function testCreateUriWithUrl(): void
    {
        $uriString = 'https://api.laposta.nl/v2/members?list_id=abcdef123456';
        $uri = $this->uriFactory->createUri($uriString);

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEquals($uriString, (string)$uri);
        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('api.laposta.nl', $uri->getHost());
        $this->assertEquals('/v2/members', $uri->getPath());
        $this->assertEquals('list_id=abcdef123456', $uri->getQuery());
    }

    protected function tearDown(): void
    {
        unset($this->uriFactory);
        parent::tearDown();
    }
}
