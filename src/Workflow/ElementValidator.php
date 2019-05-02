<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Workflow;

use FluentDOM\DOM\Element;
use Libero\ContentApiBundle\Exception\ElementNotFound;
use Libero\ContentApiBundle\Exception\TextContentMismatch;
use Libero\ContentApiBundle\Model\PutTask;
use Symfony\Component\Workflow\Event\Event;
use function trim;

trait ElementValidator
{
    final public function onValidate(Event $event) : void
    {
        /** @var PutTask $task */
        $task = $event->getSubject();

        $document = $task->getDocument();
        $xpath = $document->xpath();
        foreach ($this->namespaces() as $prefix => $uri) {
            $xpath->registerNamespace($prefix, $uri);
        }

        $id = $xpath->firstOf($this->xpath());

        if (!$id instanceof Element) {
            throw new ElementNotFound($this->xpath());
        }

        $expected = $this->expected($task);

        if ($expected !== trim($id->textContent)) {
            throw new TextContentMismatch($id, $expected);
        }
    }

    abstract protected function xpath() : string;

    abstract protected function expected(PutTask $task) : string;

    protected function namespaces() : iterable
    {
        return [];
    }
}
