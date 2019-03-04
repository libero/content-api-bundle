<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Functional;

use Symfony\Component\HttpFoundation\Request;

final class PingTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function each_service_pings(string $testCase, string $prefix) : void
    {
        static::bootKernel(['test_case' => $testCase]);

        $request = Request::create("{$prefix}/ping");

        $response = self::$kernel->handle($request);

        $this->assertSame('pong', $response->getContent());
    }

    public function serviceProvider() : iterable
    {
        yield 'Basic' => ['Basic', ''];
        yield 'Multiple service-one' => ['Multiple', '/service-one'];
        yield 'Multiple service-two' => ['Multiple', '/service-two'];
    }
}
