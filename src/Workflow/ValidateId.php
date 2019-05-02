<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Workflow;

use Libero\ContentApiBundle\Model\PutTask;

final class ValidateId
{
    use ElementValidator;

    protected function namespaces() : iterable
    {
        return ['libero' => 'http://libero.pub'];
    }

    protected function xpath() : string
    {
        return '/libero:item/libero:meta/libero:id';
    }

    protected function expected(PutTask $task) : string
    {
        return (string) $task->getItemId();
    }
}
