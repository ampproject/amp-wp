<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Tag;

/**
 * AmpBoilerplateErrorHandler - adds amp-onerror handler to disable boilerplate early on runtime error.
 *
 * This ensures that the boilerplate does not hide the content for several seconds if an error occurred
 * while loading the AMP runtime that could already be detected much earlier.
 *
 * @package ampproject/amp-toolbox
 */
final class AmpBoilerplateErrorHandler implements Transformer
{

    /**
     * XPath query to find an AMP runtime script using ES6 modules.
     *
     * Note that substring() is used as ends-with() requires XPath 2.0, but PHP comes with XPath 1.0 support only.
     *
     * @var string
     */
    const AMP_MODULAR_RUNTIME_XPATH_QUERY = './script[substring(@src, string-length(@src) - 6) = \'/v0.mjs\']';

    /**
     * Error handler script to be added to the document's <head> for AMP pages not using ES modules.
     *
     * @var string
     */
    const ERROR_HANDLER_NOMODULE = 'document.querySelector("script[src*=\'/v0.js\']").onerror=function(){'
                                   . 'document.querySelector(\'style[amp-boilerplate]\').textContent=\'\'}';

    /**
     * Error handler script to be added to the document's <head> for AMP pages using ES modules.
     *
     * @var string
     */
    const ERROR_HANDLER_MODULE = '[].slice.call(document.querySelectorAll('
                                 . '"script[src*=\'/v0.js\'],script[src*=\'/v0.mjs\']")).forEach('
                                 . 'function(s){s.onerror='
                                 . 'function(){'
                                 . 'document.querySelector(\'style[amp-boilerplate]\').textContent=\'\''
                                 . '}})';

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        if ($document->html->hasAttribute(Attribute::I_AMPHTML_NO_BOILERPLATE)) {
            // Boilerplate was removed, so no need for the amp-onerror handler.
            return;
        }

        $document->head->appendChild(
            $document->createElementWithAttributes(
                Tag::SCRIPT,
                [
                    Attribute::AMP_ONERROR => null,
                ],
                $this->usesModules($document) ? self::ERROR_HANDLER_MODULE : self::ERROR_HANDLER_NOMODULE
            )
        );
    }

    /**
     * Check whether a provided document uses ES6 modules.
     *
     * @param Document $document Document to check.
     * @return bool Whether the provided document uses ES6 modules.
     */
    private function usesModules(Document $document)
    {
        $scripts = $document->xpath->query(self::AMP_MODULAR_RUNTIME_XPATH_QUERY, $document->head);

        return $scripts->length > 0;
    }
}
