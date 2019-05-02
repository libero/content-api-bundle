<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Functional;

use Libero\ContentApiBundle\Adapter\InMemoryItems;
use Libero\ContentApiBundle\Exception\InvalidId;
use Libero\ContentApiBundle\Exception\InvalidVersionNumber;
use Libero\ContentApiBundle\Exception\ItemNotFound;
use Libero\ContentApiBundle\Exception\VersionNotFound;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Libero\ContentNegotiationBundle\Exception\NotAcceptableFormat;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;
use function tests\Libero\ContentApiBundle\stream_from_string;

final class ItemsTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function it_may_return_an_empty_list(string $testCase, string $prefix) : void
    {
        static::bootKernel(['test_case' => $testCase]);

        $request = Request::create("{$prefix}/items");

        $response = self::$kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertFalse($response->headers->has('Link'), 'Must not have a Link header');
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0" encoding="UTF-8"?><item-list xmlns="http://libero.pub"/>',
            $response->getContent()
        );
    }

    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function it_may_returns_an_empty_list_for_a_head_request(string $testCase, string $prefix) : void
    {
        static::bootKernel(['test_case' => $testCase]);

        $request = Request::create("{$prefix}/items", 'HEAD');

        $response = self::$kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertFalse($response->headers->has('Link'), 'Must not have a Link header');
        $this->assertEmpty($response->getContent());
    }

    /**
     * @test
     * @dataProvider serviceProvider
     */
    public function it_may_not_find_an_item(string $testCase, string $prefix) : void
    {
        static::bootKernel(['test_case' => $testCase]);

        $request = Request::create("{$prefix}/items/1/versions/1");

        $this->expectException(ItemNotFound::class);

        self::$kernel->handle($request);
    }

    public function serviceProvider() : iterable
    {
        yield 'Basic' => ['Basic', ''];
        yield 'Multiple service-one' => ['Multiple', '/service-one'];
        yield 'Multiple service-two' => ['Multiple', '/service-two'];
    }

    /**
     * @test
     */
    public function it_can_find_an_item_version() : void
    {
        static::bootKernel(['test_case' => 'Multiple']);

        /** @var InMemoryItems $items */
        $items = self::$container->get(InMemoryItems::class);
        $items->add(
            new ItemVersion(
                ItemId::fromString('1'),
                ItemVersionNumber::fromInt(1),
                stream_from_string('<item><front><id>1</id><version>1</version></front></item>'),
                'some-hash'
            )
        );

        $request = Request::create('/service-one/items/1/versions/1');

        $response = $this->captureContent($request, $content);

        $this->assertSame('application/xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertXmlStringEqualsXmlString(
            '<item>
                <front>
                    <id>1</id>
                    <version>1</version>
                </front>
            </item>',
            $content
        );
        $this->assertSame('private, must-revalidate', $response->headers->get('Cache-Control'));
        $this->assertSame('"some-hash"', $response->headers->get('ETag'));
    }

    /**
     * @test
     */
    public function it_revalidates_an_item_version() : void
    {
        static::bootKernel(['test_case' => 'Multiple']);

        /** @var InMemoryItems $items */
        $items = self::$container->get(InMemoryItems::class);
        $items->add(
            new ItemVersion(
                ItemId::fromString('1'),
                ItemVersionNumber::fromInt(1),
                stream_from_string('<item><front><id>1</id><version>1</version></front></item>'),
                'some-hash'
            )
        );

        $request = Request::create('/service-one/items/1/versions/1');
        $request->headers->set('If-None-Match', '"some-hash"');

        $response = $this->captureContent($request, $content);

        $this->assertSame(Response::HTTP_NOT_MODIFIED, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Content-Length'));
        $this->assertFalse($response->headers->has('Content-Type'));
        $this->assertEmpty($content);
        $this->assertSame('private, must-revalidate', $response->headers->get('Cache-Control'));
        $this->assertSame('"some-hash"', $response->headers->get('ETag'));
    }

    /**
     * @test
     */
    public function it_may_not_find_an_item_version() : void
    {
        static::bootKernel(['test_case' => 'Multiple']);

        /** @var InMemoryItems $items */
        $items = self::$container->get(InMemoryItems::class);
        $items->add(
            new ItemVersion(
                ItemId::fromString('1'),
                ItemVersionNumber::fromInt(1),
                stream_from_string('<item><front><id>1</id><version>1</version></front></item>'),
                'foo'
            )
        );

        $request = Request::create('/service-one/items/1/versions/2');

        $this->expectException(VersionNotFound::class);

        self::$kernel->handle($request);
    }

    /**
     * @test
     */
    public function it_recognises_invalid_ids() : void
    {
        static::bootKernel(['test_case' => 'Multiple']);

        $request = Request::create('/service-one/items/foo bar/versions/1');

        $this->expectException(InvalidId::class);

        self::$kernel->handle($request);
    }

    /**
     * @test
     */
    public function it_recognises_invalid_versions() : void
    {
        static::bootKernel(['test_case' => 'Multiple']);

        $request = Request::create('/service-one/items/foo/versions/foo');

        $this->expectException(InvalidVersionNumber::class);

        self::$kernel->handle($request);
    }

    /**
     * @test
     * @dataProvider typePathsProvider
     */
    public function it_will_not_negotiate_type_if_not_enabled(string $path) : void
    {
        static::bootKernel(['test_case' => 'Multiple']);

        /** @var InMemoryItems $items */
        $items = self::$container->get(InMemoryItems::class);
        $items->add(
            new ItemVersion(
                ItemId::fromString('1'),
                ItemVersionNumber::fromInt(1),
                stream_from_string('<item><front><id>1</id><version>1</version></front></item>'),
                'foo'
            )
        );

        $request = Request::create($path);
        $request->headers->set('Accept', 'application/json');

        $response = $this->captureContent($request, $content);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/xml; charset=utf-8', $response->headers->get('Content-Type'));
    }

    /**
     * @test
     * @dataProvider typePathsProvider
     */
    public function it_may_negotiate_type(string $path) : void
    {
        static::bootKernel(['test_case' => 'ContentNegotiation']);

        $request = Request::create($path);
        $request->headers->set('Accept', 'application/json');

        $this->expectException(NotAcceptableFormat::class);

        self::$kernel->handle($request);
    }

    public function typePathsProvider() : iterable
    {
        yield 'list' => ['/service-one/items'];
        yield 'item' => ['/service-one/items/1/versions/1'];
    }

    /**
     * @test
     */
    public function it_can_add_an_item() : void
    {
        static::bootKernel(['test_case' => 'Workflow']);

        $request = Request::create(
            '/items/1/versions/1',
            'PUT',
            [],
            [],
            [],
            [],
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <id>1</id>
        <service>service-one</service>
    </meta>
</item>
XML
        );

        $response = self::$kernel->handle($request);

        $this->assertSame(204, $response->getStatusCode());

        /** @var InMemoryItems $items */
        $items = self::$container->get(InMemoryItems::class);

        $this->assertCount(1, $items);
    }

    /**
     * @test
     * @dataProvider invalidItemProvider
     */
    public function it_rejects_invalid_items(string $xml) : void
    {
        static::bootKernel(['test_case' => 'Workflow']);

        $request = Request::create('/items/1/versions/1', 'PUT', [], [], [], [], $xml);

        $this->expectException(UnexpectedValueException::class);

        self::$kernel->handle($request);
    }

    public function invalidItemProvider() : iterable
    {
        yield 'no namespace' => [
            <<<XML
<item>
    <meta>
        <id>1</id>
        <service>service-one</service>
    </meta>
</item>
XML
            ,
        ];
        yield 'wrong namespace' => [
            <<<XML
<item xmlns="http://not.libero.pub">
    <meta>
        <id>1</id>
        <service>service-one</service>
    </meta>
</item>
XML
            ,
        ];

        yield 'wrong element' => [
            <<<XML
<not-item xmlns="http://libero.pub">
    <meta>
        <id>1</id>
        <service>service-one</service>
    </meta>
</not-item>
XML
            ,
        ];

        yield 'missing id' => [
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <service>service-one</service>
    </meta>
</item>
XML
            ,
        ];

        yield 'wrong id' => [
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <id>not-1</id>
        <service>service-one</service>
    </meta>
</item>
XML
            ,
        ];

        yield 'missing service' => [
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <id>1</id>
    </meta>
</item>
XML
            ,
        ];

        yield 'wrong service' => [
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <id>1</id>
        <service>not-service-one</service>
    </meta>
</item>
XML
            ,
        ];
    }
}
