<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle;

use DOMDocument;
use DOMElement;
use DOMNode;
use FluentDOM\Utility\ResourceWrapper;
use function fopen;
use function hash_final;
use function hash_init;
use function hash_update_stream;
use function rewind;
use function sprintf;

/**
 * @internal
 *
 * @return resource
 */
function stream_from_node(DOMNode $node)
{
    $document = $node instanceof DOMDocument ? $node : $node->ownerDocument;

    /** @var resource $stream */
    $stream = fopen('php://temp', 'rb+');
    $document->save(ResourceWrapper::createURI($stream));

    return $stream;
}

/**
 * @internal
 *
 * @param resource $stream
 */
function stream_hash($stream, string $algorithm = 'md5') : string
{
    rewind($stream);
    $hash = hash_init($algorithm);
    hash_update_stream($hash, $stream);

    return hash_final($hash);
}

/**
 * @internal
 */
function clark_notation(DOMElement $element) : string
{
    if (!$element->namespaceURI) {
        return $element->localName;
    }

    return sprintf('{%s}%s', $element->namespaceURI, $element->localName);
}
