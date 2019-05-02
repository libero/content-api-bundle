<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Workflow;

use FluentDOM;
use Libero\ContentApiBundle\Exception\TextContentMismatch;
use Libero\ContentApiBundle\Workflow\ValidateService;
use PHPUnit\Framework\TestCase;

final class ValidateServiceTest extends TestCase
{
    use ElementValidatorTest;

    /**
     * @test
     */
    public function it_passes_on_the_correct_service() : void
    {
        $document = FluentDOM::load(
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <service>service</service>
    </meta>
</item>
XML
        );

        $validator = new ValidateService();

        $this->expectNotToPerformAssertions();

        $validator->onValidate($this->createEvent($document));
    }

    /**
     * @test
     */
    public function it_fails_on_the_wrong_service() : void
    {
        $document = FluentDOM::load(
            <<<XML
<item xmlns="http://libero.pub">
    <meta>
        <service>other-service</service>
    </meta>
</item>
XML
        );

        $validator = new ValidateService();

        $this->expectException(TextContentMismatch::class);
        $this->expectExceptionMessage(
            'Expected {http://libero.pub}service to have the text content "service", found "other-service"'
        );

        $validator->onValidate($this->createEvent($document));
    }
}
