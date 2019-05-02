<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\EventListener;

use Libero\ContentApiBundle\EventListener\AutoTransitionListener;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

final class AutoTransitionListenerTest extends TestCase
{
    /**
     * @test
     */
    public function it_applies_the_first_available_transition() : void
    {
        $marking = new Marking();
        $subject = new stdClass();
        $transition1 = new Transition('transition1', 'place1', 'place2');
        $transition2 = new Transition('transition2', 'place3', 'place4');
        $transition3 = new Transition('transition3', 'place5', 'place6');
        $transition4 = new Transition('transition4', 'place7', 'place8');

        $workflow = $this->createMock(Workflow::class);
        $workflow->method('getEnabledTransitions')
            ->with($subject)
            ->willReturn([$transition2, $transition3, $transition4]);
        $workflow->expects($this->exactly(2))
            ->method('can')
            ->withConsecutive([$subject, 'transition2'], [$subject, 'transition3'])
            ->willReturnCallback(
                function (object $subject, string $name) : bool {
                    return 'transition3' === $name;
                }
            );
        $workflow->expects($this->once())->method('apply')->with($subject, 'transition3');

        $listener = new AutoTransitionListener($workflow);

        $listener->onEntered(new Event($subject, $marking, $transition1));
    }

    /**
     * @test
     */
    public function it_fails_if_there_is_no_available_transition() : void
    {
        $marking = new Marking();
        $subject = new stdClass();
        $transition = new Transition('transition', 'place1', 'place2');

        $workflow = $this->createMock(Workflow::class);
        $workflow->method('getEnabledTransitions')->with($subject)->willReturn([]);
        $workflow->expects($this->never())->method('apply');

        $listener = new AutoTransitionListener($workflow);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No transition can be applied');

        $listener->onEntered(new Event($subject, $marking, $transition));
    }
}
