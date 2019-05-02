<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Exception;

use FluentDOM\DOM\Document;
use Libero\ContentApiBundle\Exception\TextContentMismatch;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class TextContentMismatchTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_unexpected_value() : void
    {
        $actual = (new Document())->createElementNS('actual-namespace', 'actual-name', 'actual-value');
        $invalidDocumentElement = new TextContentMismatch($actual, 'expected-value');

        $this->assertInstanceOf(UnexpectedValueException::class, $invalidDocumentElement);
        $this->assertSame(
            'Expected {actual-namespace}actual-name to have the text content "expected-value", found "actual-value"',
            $invalidDocumentElement->getMessage()
        );
    }

    /**
     * @test
     */
    public function it_has_the_actual_element() : void
    {
        $actual = (new Document())->createElementNS('actual-namespace', 'actual-name', 'actual-value');
        $invalidDocumentElement = new TextContentMismatch($actual, 'expected-value');

        $this->assertEquals($actual, $invalidDocumentElement->getElement());
    }

    /**
     * @test
     */
    public function it_has_the_expected_value() : void
    {
        $actual = (new Document())->createElementNS('actual-namespace', 'actual-name', 'actual-value');
        $invalidDocumentElement = new TextContentMismatch($actual, 'expected-value');

        $this->assertEquals('expected-value', $invalidDocumentElement->getExpected());
    }
}
