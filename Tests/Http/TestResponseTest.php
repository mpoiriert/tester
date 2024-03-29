<?php

namespace Draw\Component\Tester\Tests\Http;

use Draw\Component\Tester\DataTester;
use Draw\Component\Tester\Http\TestResponse;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class TestResponseTest extends TestCase
{
    /**
     * @return TestResponse
     */
    private function createTestResponse(ResponseInterface $response)
    {
        return new TestResponse(new Request('GET', '/test'), $response);
    }

    public static function provideAssertSuccessful(): array
    {
        return [
            // 2xx
            '200' => [200, true],
            '201' => [201, true],
            '202' => [202, true],
            '203' => [203, true],
            '204' => [204, true],
            '205' => [205, true],
            '206' => [206, true],
            '207' => [207, true],
            '208' => [208, true],
            '226' => [226, true],
            // 3xx
            '300' => [300, true],
            '301' => [301, true],
            '302' => [302, true],
            '303' => [303, true],
            '304' => [304, true],
            '305' => [305, true],
            '306' => [306, true],
            '307' => [307, true],
            '308' => [308, true],
            // 4xx
            '400' => [400, false],
            '401' => [401, false],
            '402' => [402, false],
            '403' => [403, false],
            '404' => [404, false],
            '405' => [405, false],
            '406' => [406, false],
            '407' => [407, false],
            '408' => [408, false],
            // 5xx
            '500' => [500, false],
            '501' => [501, false],
            '502' => [502, false],
            '503' => [503, false],
            '504' => [504, false],
            '505' => [505, false],
            '506' => [506, false],
            '507' => [507, false],
            '508' => [508, false],
            '510' => [510, false],
            '511' => [511, false],
        ];
    }

    /**
     * @dataProvider provideAssertSuccessful
     */
    public function testAssertSuccessful(int $statusCode, bool $expectSuccess): void
    {
        $testResponse = $this->createTestResponse(new Response($statusCode));

        if (!$expectSuccess) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage('Response status code ['.$statusCode.'] is not a successful status code.');
        }

        // We check fluent interface
        static::assertSame(
            $testResponse,
            $testResponse->assertSuccessful()
        );
    }

    public function testAssertStatusPass(): void
    {
        $testResponse = $this->createTestResponse(new Response(100));

        // We check fluent interface
        static::assertSame(
            $testResponse,
            $testResponse->assertStatus(100)
        );
    }

    public function testAssertStatusFail(): void
    {
        $testResponse = $this->createTestResponse(new Response(100));

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Expected status code [666] but received [100].');

        $testResponse->assertStatus(666);
    }

    public static function provideAssertRedirectStatusCode(): array
    {
        return [
            // 2xx
            '200' => [200, false],
            '201' => [201, false],
            '202' => [202, false],
            '203' => [203, false],
            '204' => [204, false],
            '205' => [205, false],
            '206' => [206, false],
            '207' => [207, false],
            '208' => [208, false],
            '226' => [226, false],
            // 3xx fail
            '300' => [300, false],
            '304' => [304, false],
            '305' => [305, false],
            '306' => [306, false],
            // 3xx pass
            '301' => [301, true],
            '302' => [302, true],
            '303' => [303, true],
            '307' => [307, true],
            '308' => [308, true],
            // 4xx
            '400' => [400, false],
            '401' => [401, false],
            '402' => [402, false],
            '403' => [403, false],
            '404' => [404, false],
            '405' => [405, false],
            '406' => [406, false],
            '407' => [407, false],
            '408' => [408, false],
            // 5xx
            '500' => [500, false],
            '501' => [501, false],
            '502' => [502, false],
            '503' => [503, false],
            '504' => [504, false],
            '505' => [505, false],
            '506' => [506, false],
            '507' => [507, false],
            '508' => [508, false],
            '510' => [510, false],
            '511' => [511, false],
        ];
    }

    /**
     * @dataProvider provideAssertRedirectStatusCode
     */
    public function testAssertRedirectStatusCode(int $statusCode, bool $expectSuccess): void
    {
        $testResponse = $this->createTestResponse(new Response($statusCode));

        if (!$expectSuccess) {
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage('Response status code ['.$statusCode.'] is not a redirect status code.');
        }

        $testResponse->assertRedirect();
    }

    public function testAssertRedirectUriPass(): void
    {
        $testResponse = $this->createTestResponse(
            new Response(301, ['location' => ['/redirect-to']])
        );

        // We check fluent interface
        static::assertSame(
            $testResponse,
            $testResponse->assertRedirect('/redirect-to')
        );
    }

    public function testAssertRedirectUriFail(): void
    {
        $testResponse = $this->createTestResponse(
            new Response(301, ['location' => ['/redirect-elsewhere']])
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            "The header [location] does not contain the value [/redirect-to]. Values are:\n/redirect-elsewhere"
        );

        $testResponse->assertRedirect('/redirect-to');
    }

    public static function provideAssertHeader(): array
    {
        return [
            'pass-one-value' => ['header', 'value', ['header' => ['value']], true],
            'fail-no-header' => ['header', 'value', [], false],
            'fail-wrong-header' => ['header', 'value', ['wrong-header' => ['value']], false],
            'fail-wrong-value' => ['header', 'value', ['header' => ['wrong-value']], false],
            'fail-multiple-wrong-value' => ['header', 'value', ['header' => ['wrong-value-1', 'wrong-value-2']], false],
            'pass-multiple-value' => ['header', 'value', ['header' => ['value', 'other-value']], true],
            'pass-multiple-header' => ['header', 'value', ['header' => ['value'], 'other-header' => ['other-value']], true],
            'fail-multiple-wrong-header' => ['header', 'value', ['wrong-header-1' => ['value'], 'wrong-header-2' => ['value']], false],
        ];
    }

    /**
     * @dataProvider provideAssertHeader
     *
     * @phpstan-param array<string, mixed> $headers
     */
    public function testAssertHeader(string $headerName, mixed $value, array $headers, bool $expectedPass): void
    {
        $testResponse = $this->createTestResponse(new Response(200, $headers));

        if (!$expectedPass) {
            $this->expectException(ExpectationFailedException::class);
            if (!$testResponse->getResponse()->hasHeader($headerName)) {
                $this->expectExceptionMessage("Header [{$headerName}] not present on response.");
            } else {
                $this->expectExceptionMessage(
                    sprintf(
                        "The header [%s] does not contain the value [%s]. Values are:\n%s",
                        $headerName,
                        $value,
                        implode("\n", $testResponse->getResponse()->getHeader($headerName))
                    )
                );
            }
        }

        // We check fluent interface
        static::assertSame(
            $testResponse,
            $testResponse->assertHeader($headerName, $value)
        );
    }

    public function testGetResponseBodyContents(): void
    {
        $testResponse = $this->createTestResponse(new Response(200, [], 'body'));

        static::assertSame(
            'body',
            $testResponse->getResponseBodyContents()
        );

        // Multiple call should return the same content
        static::assertSame(
            'body',
            $testResponse->getResponseBodyContents()
        );
    }

    public function testAssertCookie(): void
    {
        $testResponse = $this->createTestResponse(
            new Response(200, ['Set-Cookie' => ['name=value']])
        );

        // We check fluent interface
        static::assertSame(
            $testResponse,
            $testResponse->assertCookie('name')
        );
    }

    public function testAssertCookieValue(): void
    {
        $testResponse = $this->createTestResponse(
            new Response(200, ['Set-Cookie' => ['name=value']])
        );

        // We check fluent interface
        static::assertSame(
            $testResponse,
            $testResponse->assertCookie('name', 'value')
        );
    }

    public function testAssertCookieNotFound(): void
    {
        $testResponse = $this->createTestResponse(
            new Response(200, ['Set-Cookie' => ['name=value']])
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Cookie [%s] not present on response. Possible cookies name are:\n%s",
                'other-cookie',
                'name'
            )
        );

        $testResponse->assertCookie('other-cookie');
    }

    public function testAssertCookieValueNotMatch(): void
    {
        $testResponse = $this->createTestResponse(
            new Response(200, ['Set-Cookie' => ['name=value']])
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cookie [%s] was found, but value [%s] does not match [%s].',
                'name',
                'value',
                'other-value'
            )
        );

        $testResponse->assertCookie('name', 'other-value');
    }

    public function testCreateJsonDataTester(): void
    {
        $testResponse = $this->createTestResponse(
            new Response(200, [], '{}')
        );

        static::assertInstanceOf(
            DataTester::class,
            $testResponse->toJsonDataTester()
        );
    }
}
