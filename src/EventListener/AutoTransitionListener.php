<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\EventListener;

use LogicException;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use function array_map;

final class AutoTransitionListener
{
    private $workflow;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function onEntered(Event $event) : void
    {
        if ($event->getMarking()->has('end')) {
            return;
        }

        $subject = $event->getSubject();

        $transitions = array_map(
            function (Transition $transition) : string {
                return $transition->getName();
            },
            $this->workflow->getEnabledTransitions($subject)
        );

        foreach ($transitions as $transition) {
            if (!$this->workflow->can($subject, $transition)) {
                continue;
            }

            $this->workflow->apply($subject, $transition);

            return;
        }

        throw new LogicException('No transition can be applied');
    }
}
