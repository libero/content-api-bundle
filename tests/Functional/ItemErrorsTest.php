<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Functional;

use Libero\ContentApiBundle\Adapter\InMemoryItems;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Symfony\Component\HttpFoundation\Request;
use function tests\Libero\ContentApiBundle\stream_from_string;

final class ItemErrorsTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_may_not_find_an_item() : void
    {
        static::bootKernel(['test_case' => 'ApiProblem']);

        $request = Request::create('/service/items/1/versions/1');

        $response = self::$kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/problem+xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('en', $response->headers->get('Content-Language'));
        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="en" xmlns="urn:ietf:rfc:7807">
                <status>404</status>
                <title>Item not found</title>
                <details>An item with the ID "1" could not be found.</details>
            </problem>',
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function it_may_not_find_an_item_version() : void
    {
        static::bootKernel(['test_case' => 'ApiProblem']);

        /** @var InMemoryItems $items */
        $items = self::$container->get(InMemoryItems::class);
        $items->add(
            new ItemVersion(
                ItemId::fromString('1'),
                ItemVersionNumber::fromInt(1),
                stream_from_string('foo'),
                'foo'
            )
        );

        $request = Request::create('/service/items/1/versions/2');

        $response = self::$kernel->handle($request);

        $this->assertSame('application/problem+xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('en', $response->headers->get('Content-Language'));
        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="en" xmlns="urn:ietf:rfc:7807">
                <status>404</status>
                <title>Item version not found</title>
                <details>Item "1" does not have a version 2.</details>
            </problem>',
            $response->getContent()
        );
        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
    }

    /**
     * @test
     */
    public function it_recognises_invalid_ids() : void
    {
        static::bootKernel(['test_case' => 'ApiProblem']);

        $request = Request::create('/service/items/foo bar/versions/1');

        $response = self::$kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/problem+xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('en', $response->headers->get('Content-Language'));
        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="en" xmlns="urn:ietf:rfc:7807">
                <status>400</status>
                <title>Invalid ID</title>
                <details>"foo bar" is not a valid ID.</details>
            </problem>',
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function it_recognises_invalid_versions() : void
    {
        static::bootKernel(['test_case' => 'ApiProblem']);

        $request = Request::create('/service/items/foo/versions/foo');

        $response = self::$kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/problem+xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('en', $response->headers->get('Content-Language'));
        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="en" xmlns="urn:ietf:rfc:7807">
                <status>400</status>
                <title>Invalid version number</title>
                <details>"foo" is not a valid version number.</details>
            </problem>',
            $response->getContent()
        );
    }

    /**
     * @test
     * @dataProvider invalidItemProvider
     */
    public function it_recognises_invalid_put_requests(string $xml, string $title, string $details) : void
    {
        static::bootKernel(['test_case' => 'ApiProblem']);

        $request = Request::create('/service/items/1/versions/1', 'PUT', [], [], [], [], $xml);

        $response = self::$kernel->handle($request);

        $this->assertSame('no-cache, private', $response->headers->get('Cache-Control'));
        $this->assertSame('application/problem+xml; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('en', $response->headers->get('Content-Language'));
        $this->assertXmlStringEqualsXmlString(
            <<<XML
<problem xml:lang="en" xmlns="urn:ietf:rfc:7807">
    <status>400</status>
    <title>{$title}</title>
    <details>{$details}</details>
</problem>
XML
            ,
            $response->getContent()
        );
    }

    public function invalidItemProvider() : iterable
    {
        yield 'no namespace' => [
            <<<XML
<item>
    <meta>
        <id>1</id>
        <service>service</service>
    </meta>
</item>
XML
            ,
            'Invalid document element',
            'Expected "{http://libero.pub}item" as the document element, found "item".',
        ];
        yield 'wrong namespace' => [
            <<<XML
<item xmlns="http://not.libero.pub">
    <meta>
        <id>1</id>
        <service>service</service>
    </meta>
</item>
XML
            ,
            'Invalid document element',
            'Expected "{http://libero.pub}item" as the document element, found "{http://not.libero.pub}item".',
        ];

        yield 'wrong element' => [
            <<<XML
<not-item xmlns="http://libero.pub">
    <meta>
        <id>1</id>
        <service>service</service>
    </meta>
</not-item>
XML
            ,
            'Invalid document element',
            'Expected "{http://libero.pub}item" as the document element, found "{http://libero.pub}not-item".',
        ];

        yield 'missing id' => [
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <service>service</service>
    </meta>
</item>
XML
            ,
            'Element not found',
            'The "/libero:item/libero:meta/libero:id" element could not be found.',
        ];

        yield 'wrong id' => [
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <id>not-1</id>
        <service>service</service>
    </meta>
</item>
XML
            ,
            'Invalid element value',
            'Expected {http://libero.pub}id to have the value "1", found "not-1".',
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
            'Element not found',
            'The "/libero:item/libero:meta/libero:service" element could not be found.',
        ];

        yield 'wrong service' => [
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <id>1</id>
        <service>not-service</service>
    </meta>
</item>
XML
            ,
            'Invalid element value',
            'Expected {http://libero.pub}service to have the value "service", found "not-service".',
        ];
    }
}
