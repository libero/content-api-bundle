<?php

declare(strict_types=1);

namespace tests\Libero\ContentApiBundle\EventListener;

use Exception;
use FluentDOM\DOM\Document;
use Libero\ApiProblemBundle\Event\CreateApiProblem;
use Libero\ContentApiBundle\EventListener\TextContentMismatchListener;
use Libero\ContentApiBundle\Exception\TextContentMismatch;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

final class TextContentMismatchListenerTest extends TestCase
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
                'libero.content.item.text_content_mismatch.title' => 'es title',
                'libero.content.item.text_content_mismatch.details' => 'es details: %element% %actual% %expected%',
            ],
            'es',
            'api_problem'
        );

        $listener = new TextContentMismatchListener($translator);

        $request = new Request();
        $request->setLocale('es');

        $actual = (new Document())->createElementNS('actual-namespace', 'actual-name', 'actual-value');

        $event = new CreateApiProblem($request, new TextContentMismatch($actual, 'expected-value'));

        $listener->onCreateApiProblem($event);

        $this->assertXmlStringEqualsXmlString(
            '<problem xml:lang="es" xmlns="urn:ietf:rfc:7807">
                <status>400</status>
                <title>es title</title>
                <details>es details: {actual-namespace}actual-name actual-value expected-value</details>
            </problem>',
            $event->getDocument()->saveXML()
        );
    }

    /**
     * @test
     */
    public function it_ignores_other_exceptions() : void
    {
        $listener = new TextContentMismatchListener(new IdentityTranslator());
        $event = new CreateApiProblem(new Request, new Exception());

        $expected = $event->getDocument()->saveXML();

        $listener->onCreateApiProblem($event);

        $this->assertXmlStringEqualsXmlString(
            $expected,
            $event->getDocument()->saveXML()
        );
    }
}
