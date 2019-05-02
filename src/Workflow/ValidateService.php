<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Workflow;

use Libero\ContentApiBundle\Model\PutTask;

final class ValidateService
{
    use ElementValidator;

    protected function namespaces() : iterable
    {
        return ['libero' => 'http://libero.pub'];
    }

    protected function xpath() : string
    {
        return '/libero:item/libero:meta/libero:service';
    }

    protected function expected(PutTask $task) : string
    {
        return $task->getService();
    }
}
