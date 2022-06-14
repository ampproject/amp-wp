<?php

namespace AmpProject\Validator;

use AmpProject\Dom\Document;
use AmpProject\Format;
use AmpProject\Html\Parser\HtmlParser;

/**
 * Validator engine that checks adherence to spec rules against provided markup.
 *
 * @package ampproject/amp-toolbox
 */
final class ValidationEngine
{
    /**
     * Validate the provided DOM document.
     *
     * @param Document $document   DOM document to apply the transformations to.
     * @param string   $htmlFormat Optional. AMP HTML format to validate against. Defaults to 'AMP'.
     * @return ValidationResult Result of the validation.
     */
    public function validateDom(Document $document, $htmlFormat = Format::AMP)
    {
        return $this->validateHtml($document->saveHTML());
    }

    /**
     * Validate the provided string of HTML markup.
     *
     * @param string $html       HTML markup to apply the transformations to.
     * @param string $htmlFormat Optional. AMP HTML format to validate against. Defaults to 'AMP'.
     * @return ValidationResult Result of the validation.
     */
    public function validateHtml($html, $htmlFormat = Format::AMP)
    {
        $parser  = new HtmlParser();
        $spec    = new Spec();
        $handler = new ValidationHandler($htmlFormat, $spec);

        $parser->parse($handler, $html);

        return $handler->getResult();
    }
}
