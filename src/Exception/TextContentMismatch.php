<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Exception;

use FluentDOM\DOM\Element;
use Throwable;
use UnexpectedValueException;
use function Libero\ContentApiBundle\clark_notation;
use function sprintf;
use function trim;

class TextContentMismatch extends UnexpectedValueException
{
    private $element;
    private $expected;

    public function __construct(Element $element, string $expected, ?Throwable $previous = null, int $code = 0)
    {
        parent::__construct(
            sprintf(
                'Expected %s to have the text content "%s", found "%s"',
                clark_notation($element),
                $expected,
                trim($element->textContent)
            ),
            $code,
            $previous
        );

        $this->element = $element;
        $this->expected = $expected;
    }

    public function getElement() : Element
    {
        return $this->element;
    }

    public function getExpected() : string
    {
        return $this->expected;
    }
}
