<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Controller;

use FluentDOM;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\Items;
use Libero\ContentApiBundle\Model\ItemVersion;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Libero\ContentApiBundle\Model\PutTask;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Workflow;
use UnexpectedValueException;
use function Libero\ContentApiBundle\stream_from_node;
use function Libero\ContentApiBundle\stream_hash;

final class PutItemController
{
    private $workflow;
    private $items;
    private $service;

    public function __construct(Workflow $workflow, Items $items, string $service)
    {
        $this->workflow = $workflow;
        $this->items = $items;
        $this->service = $service;
    }

    public function __invoke(Request $request, string $id, string $version) : Response
    {
        $id = ItemId::fromString($id);
        $version = ItemVersionNumber::fromString($version);

        $task = new PutTask($this->service, $id, $version, FluentDOM::load($request->getContent()));

        $this->workflow->apply($task, 'start');

        if ('end' !== $task->getState()) {
            throw new UnexpectedValueException("Expected state 'end', got '{$task->getState()}'.");
        }

        $stream = stream_from_node($task->getDocument());
        $this->items->add(new ItemVersion($id, $version, $stream, stream_hash($stream)));

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
