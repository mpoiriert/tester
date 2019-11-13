<?php namespace Draw\Component\Tester\Http;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class RequestFactory implements RequestFactoryInterface
{
    public function createRequest($method, $uri, $body = null, array $headers = [], $version = '1.1'): RequestInterface
    {
        return new Request($method, $uri, $headers, $body, $version);
    }
}