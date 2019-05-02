<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\EventListener;

use Exception;
use FluentDOM\DOM\Document;
use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\EventListener\InvalidDocumentElementListener;
use Libero\ContentApiBundle\Exception\InvalidDocumentElement;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

final class InvalidDocumentElementListenerTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_translated_properties() : void
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource(
            'array',
            [
                'libero.content.item.invalid_document_element.title' => 'es title',
                'libero.content.item.invalid_document_element.details' => 'es details: %actual% %expected%',
            ],
            'es',
            'api_problem'
        );

        $listener = new InvalidDocumentElementListener($translator);

        $request = new Request();
        $request->setLocale('es');

        $actual = (new Document())->createElementNS('actual-namespace', 'actual');

        $event = new CreateApiProblem($request, new InvalidDocumentElement($actual, '{expected-namespace}expected'));

        $listener->onCreateApiProblem($event);

        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="es" xmlns="urn:ietf:rfc:7807">
                <status>400</status>
                <title>es title</title>
                <details>es details: {actual-namespace}actual {expected-namespace}expected</details>
            </problem>',
            $event->getDocument()->saveXML()
        );
    }

    /**
     * @test
     */
    public function it_ignores_other_exceptions() : void
    {
        $listener = new InvalidDocumentElementListener(new IdentityTranslator());
        $event = new CreateApiProblem(new Request, new Exception());

        $expected = $event->getDocument()->saveXML();

        $listener->onCreateApiProblem($event);

        $this->assertXmlStringEqualsXmlString(
            $expected,
            $event->getDocument()->saveXML()
        );
    }
}
