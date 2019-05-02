<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Workflow;

use FluentDOM;
use Libero\ContentApiBundle\Exception\TextContentMismatch;
use Libero\ContentApiBundle\Workflow\ValidateId;
use PHPUnit\Framework\TestCase;

final class ValidateIdTest extends TestCase
{
    use ElementValidatorTest;

    /**
     * @test
     */
    public function it_passes_on_the_correct_id() : void
    {
        $document = FluentDOM::load(
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <id>id</id>
    </meta>
</item>
XML
        );

        $validator = new ValidateId();

        $this->expectNotToPerformAssertions();

        $validator->onValidate($this->createEvent($document));
    }

    /**
     * @test
     */
    public function it_fails_on_the_wrong_id() : void
    {
        $document = FluentDOM::load(
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <id>other-id</id>
    </meta>
</item>
XML
        );

        $validator = new ValidateId();

        $this->expectException(TextContentMismatch::class);
        $this->expectExceptionMessage('Expected {http://libero.pub}id to have the text content "id", found "other-id"');

        $validator->onValidate($this->createEvent($document));
    }
}
