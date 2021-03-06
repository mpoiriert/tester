<?php

namespace Draw\Component\Tester\Tests\Http\Cookie;

use Draw\Component\Tester\Http\ClientObserver;
use Draw\Component\Tester\Http\Cookie\CookieClientObserver;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CookieClientObserverTest extends TestCase
{
    public function testConstruct()
    {
        $cookieClientObserver = new CookieClientObserver();

        $this->assertInstanceOf(ClientObserver::class, $cookieClientObserver);

        return $cookieClientObserver;
    }

    /**
     * @depends testConstruct
     *
     * @return CookieClientObserver
     */
    public function testRequestNoCookie(CookieClientObserver $clientObserver)
    {
        $request = $clientObserver->preSendRequest(new Request('GET', 'http://locahhost/test'));

        $this->assertInstanceOf(RequestInterface::class, $request);

        $this->assertFalse($request->hasHeader('Cookie'));

        return $clientObserver;
    }

    /**
     * @depends testRequestNoCookie
     *
     * @return CookieClientObserver
     */
    public function testResponseWithCookie(CookieClientObserver $clientObserver)
    {
        $response = $clientObserver->postSendRequest(
            new Request('GET', 'http://locahhost/test'),
            new Response(200, ['Set-Cookie' => 'name=value'])
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);

        return $clientObserver;
    }

    /**
     * @depends testResponseWithCookie
     *
     * @return CookieClientObserver
     */
    public function testRequestWithCookie(CookieClientObserver $clientObserver)
    {
        $request = $clientObserver->preSendRequest(new Request('GET', 'http://locahhost/test'));

        $this->assertInstanceOf(RequestInterface::class, $request);

        $this->assertTrue($request->hasHeader('Cookie'));
        $this->assertContains('name=value', $request->getHeader('Cookie'));

        return $clientObserver;
    }

    /**
     * @depends testRequestWithCookie
     *
     * @return CookieClientObserver
     */
    public function testRemoveCookie(CookieClientObserver $clientObserver)
    {
        $response = $clientObserver->postSendRequest(
            new Request('GET', 'http://locahhost/test'),
            new Response(200, ['Set-Cookie' => 'name='])
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);

        return $clientObserver;
    }

    /**
     * @depends testRemoveCookie
     *
     * @return CookieClientObserver
     */
    public function testRequestCookieRemoved(CookieClientObserver $clientObserver)
    {
        return $this->testRequestNoCookie($clientObserver);
    }
}
