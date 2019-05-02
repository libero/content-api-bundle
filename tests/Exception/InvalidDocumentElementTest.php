<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Exception;

use FluentDOM\DOM\Document;
use Libero\ContentApiBundle\Exception\InvalidDocumentElement;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class InvalidDocumentElementTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_unexpected_value() : void
    {
        $actual = (new Document())->createElementNS('actual-namespace', 'actual');
        $invalidDocumentElement = new InvalidDocumentElement($actual, '{expected-namespace}expected');

        $this->assertInstanceOf(UnexpectedValueException::class, $invalidDocumentElement);
        $this->assertSame(
            'Unexpected document element {actual-namespace}actual',
            $invalidDocumentElement->getMessage()
        );
    }

    /**
     * @test
     */
    public function it_has_the_actual_element() : void
    {
        $actual = (new Document())->createElementNS('actual-namespace', 'actual');
        $invalidDocumentElement = new InvalidDocumentElement($actual, '{expected-namespace}expected');

        $this->assertEquals($actual, $invalidDocumentElement->getElement());
    }

    /**
     * @test
     */
    public function it_has_the_expected_element_name() : void
    {
        $actual = (new Document())->createElementNS('actual-namespace', 'actual');
        $invalidDocumentElement = new InvalidDocumentElement($actual, '{expected-namespace}expected');

        $this->assertEquals('{expected-namespace}expected', $invalidDocumentElement->getExpected());
    }
}
