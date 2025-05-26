<?php

declare(strict_types=1);

namespace LapostaApi\Tests\Unit\Http;

use LapostaApi\Http\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public function testConstructorParsesUriComponents(): void
    {
        $uriString = 'https://user:pass@example.com:8080/path?query=value#fragment';

        $uri = new Uri($uriString);

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame('query=value', $uri->getQuery());
        $this->assertSame('fragment', $uri->getFragment());
    }

    public function testGetAuthority(): void
    {
        $uriString = 'https://user:pass@example.com:8080';
        $uri = new Uri($uriString);

        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
    }

    public function testGetAuthorityWithoutUserInfo(): void
    {
        $uriString = 'https://example.com:8080';
        $uri = new Uri($uriString);

        $this->assertSame('example.com:8080', $uri->getAuthority());
    }

    public function testGetAuthorityWithoutPort(): void
    {
        $uriString = 'https://example.com';
        $uri = new Uri($uriString);

        $this->assertSame('example.com', $uri->getAuthority());
    }

    public function testWithScheme(): void
    {
        $uri = new Uri('http://example.com');
        $newUri = $uri->withScheme('https');

        $this->assertSame('http', $uri->getScheme()); // Original remains unchanged
        $this->assertSame('https', $newUri->getScheme());
    }

    public function testWithUserInfo(): void
    {
        $uri = new Uri('http://example.com');
        $newUri = $uri->withUserInfo('user', 'pass');

        $this->assertSame('', $uri->getUserInfo()); // Original remains unchanged
        $this->assertSame('user:pass', $newUri->getUserInfo());
    }

    public function testWithHost(): void
    {
        $uri = new Uri('http://example.com');
        $newUri = $uri->withHost('newhost.com');

        $this->assertSame('example.com', $uri->getHost()); // Original remains unchanged
        $this->assertSame('newhost.com', $newUri->getHost());
    }

    public function testWithPort(): void
    {
        $uri = new Uri('http://example.com');
        $newUri = $uri->withPort(8080);

        $this->assertNull($uri->getPort()); // Original remains unchanged
        $this->assertSame(8080, $newUri->getPort());
    }

    public function testWithPath(): void
    {
        $uri = new Uri('http://example.com/old-path');
        $newUri = $uri->withPath('/new-path');

        $this->assertSame('/old-path', $uri->getPath()); // Original remains unchanged
        $this->assertSame('/new-path', $newUri->getPath());
    }

    public function testWithQuery(): void
    {
        $uri = new Uri('http://example.com?old=query');
        $newUri = $uri->withQuery('new=query');

        $this->assertSame('old=query', $uri->getQuery()); // Original remains unchanged
        $this->assertSame('new=query', $newUri->getQuery());
    }

    public function testWithFragment(): void
    {
        $uri = new Uri('http://example.com#old-fragment');
        $newUri = $uri->withFragment('new-fragment');

        $this->assertSame('old-fragment', $uri->getFragment()); // Original remains unchanged
        $this->assertSame('new-fragment', $newUri->getFragment());
    }

    public function testToString(): void
    {
        $uriString = 'https://user:pass@example.com:8080/path?query=value#fragment';
        $uri = new Uri($uriString);

        $this->assertSame($uriString, (string)$uri);
    }

    public function testToStringWithoutOptionalComponents(): void
    {
        $uri = new Uri('http://example.com');

        $this->assertSame('http://example.com', (string)$uri);
    }

    public function testWithEmptyComponents(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', (string)$uri);
    }
}
