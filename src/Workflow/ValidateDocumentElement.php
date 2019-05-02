<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Workflow;

use FluentDOM\DOM\Element;
use Libero\ContentApiBundle\Exception\InvalidDocumentElement;
use Libero\ContentApiBundle\Model\PutTask;
use Symfony\Component\Workflow\Event\Event;
use function Libero\ContentApiBundle\clark_notation;

final class ValidateDocumentElement
{
    private const EXPECTED = '{http://libero.pub}item';

    public function onValidate(Event $event) : void
    {
        /** @var PutTask $task */
        $task = $event->getSubject();

        /** @var Element $documentElement */
        $documentElement = $task->getDocument()->documentElement;

        if (self::EXPECTED !== clark_notation($documentElement)) {
            throw new InvalidDocumentElement($documentElement, self::EXPECTED);
        }
    }
}
