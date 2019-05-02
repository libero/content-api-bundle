<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\Model;

use FluentDOM\DOM\Document;

final class PutTask
{
    private $document;
    private $itemId;
    private $itemVersion;
    private $service;
    private $state;

    public function __construct(string $service, ItemId $itemId, ItemVersionNumber $itemVersion, Document $document)
    {
        $this->service = $service;
        $this->itemId = $itemId;
        $this->itemVersion = $itemVersion;
        $this->document = $document;
        $this->state = 'start';
    }

    public function getService() : string
    {
        return $this->service;
    }

    public function getItemId() : ItemId
    {
        return $this->itemId;
    }

    public function getItemVersion() : ItemVersionNumber
    {
        return $this->itemVersion;
    }

    public function getDocument() : Document
    {
        return $this->document;
    }

    public function getState() : string
    {
        return $this->state;
    }

    public function setState(string $state) : void
    {
        $this->state = $state;
    }
}
