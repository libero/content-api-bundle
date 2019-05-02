<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Workflow;

use FluentDOM\DOM\Document;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Libero\ContentApiBundle\Model\PutTask;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

trait ElementValidatorTest
{
    private function createEvent(Document $document) : Event
    {
        $marking = new Marking();
        $task = new PutTask('service', ItemId::fromString('id'), ItemVersionNumber::fromInt(1), $document);
        $transition = new Transition('transition', 'place1', 'place2');

        return new Event($task, $marking, $transition);
    }
}
