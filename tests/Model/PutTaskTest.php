<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Model;

use FluentDOM\DOM\Document;
use Libero\ContentApiBundle\Model\ItemId;
use Libero\ContentApiBundle\Model\ItemVersionNumber;
use Libero\ContentApiBundle\Model\PutTask;
use PHPUnit\Framework\TestCase;

final class PutTaskTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_service() : void
    {
        $task = new PutTask('service', ItemId::fromString('id'), ItemVersionNumber::fromInt(1), new Document());

        $this->assertSame('service', $task->getService());
    }

    /**
     * @test
     */
    public function it_has_an_item_id() : void
    {
        $task = new PutTask('service', ItemId::fromString('id'), ItemVersionNumber::fromInt(1), new Document());

        $this->assertEquals(ItemId::fromString('id'), $task->getItemId());
    }

    /**
     * @test
     */
    public function it_has_an_item_version() : void
    {
        $task = new PutTask('service', ItemId::fromString('id'), ItemVersionNumber::fromInt(1), new Document());

        $this->assertEquals(ItemVersionNumber::fromInt(1), $task->getItemVersion());
    }

    /**
     * @test
     */
    public function it_has_a_document() : void
    {
        $task = new PutTask('service', ItemId::fromString('id'), ItemVersionNumber::fromInt(1), new Document());

        $this->assertEquals(new Document(), $task->getDocument());
    }

    /**
     * @test
     */
    public function it_has_a_state() : void
    {
        $task = new PutTask('service', ItemId::fromString('id'), ItemVersionNumber::fromInt(1), new Document());

        $this->assertSame('start', $task->getState());

        $task->setState('end');

        $this->assertSame('end', $task->getState());
    }
}
