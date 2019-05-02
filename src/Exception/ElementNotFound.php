<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Exception;

use Throwable;
use UnexpectedValueException;

class ElementNotFound extends UnexpectedValueException
{
    private $element;

    public function __construct(string $element, ?Throwable $previous = null, int $code = 0)
    {
        parent::__construct("The {$element} element could not be found", $code, $previous);

        $this->element = $element;
    }

    public function getElement() : string
    {
        return $this->element;
    }
}
