<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\Workflow;

use FluentDOM;
use Libero\ContentApiBundle\Exception\InvalidDocumentElement;
use Libero\ContentApiBundle\Workflow\ValidateDocumentElement;
use PHPUnit\Framework\TestCase;

final class ValidateDocumentElementTest extends TestCase
{
    use ElementValidatorTest;

    /**
     * @test
     */
    public function it_passes_on_the_correct_document_element() : void
    {
        $document = FluentDOM::load('<item xmlns="http://libero.pub"/>');

        $validator = new ValidateDocumentElement();

        $this->expectNotToPerformAssertions();

        $validator->onValidate($this->createEvent($document));
    }

    /**
     * @test
     */
    public function it_fails_on_the_wrong_document_element() : void
    {
        $document = FluentDOM::load('<item xmlns="http://not.libero.pub"/>');

        $validator = new ValidateDocumentElement();

        $this->expectException(InvalidDocumentElement::class);
        $this->expectExceptionMessage('Unexpected document element {http://not.libero.pub}item');

        $validator->onValidate($this->createEvent($document));
    }
}
