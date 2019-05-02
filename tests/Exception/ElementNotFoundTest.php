<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Exception;

use Libero\ContentApiBundle\Exception\ElementNotFound;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class ElementNotFoundTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_unexpected_value() : void
    {
        $elementNotFound = new ElementNotFound('{namespace}name');

        $this->assertInstanceOf(UnexpectedValueException::class, $elementNotFound);
        $this->assertSame('The {namespace}name element could not be found', $elementNotFound->getMessage());
    }

    /**
     * @test
     */
    public function it_has_the_element_name() : void
    {
        $elementNotFound = new ElementNotFound('{namespace}name');

        $this->assertSame('{namespace}name', $elementNotFound->getElement());
    }
}
