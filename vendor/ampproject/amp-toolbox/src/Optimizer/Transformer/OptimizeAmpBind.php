<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Html\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Dom\NodeWalker;
use AmpProject\Extension;
use AmpProject\Optimizer\Configuration\OptimizeAmpBindConfiguration;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\Html\Tag;
use DOMAttr;

/**
 * OptimizeAmpBind - inject a querySelectorAll query-able i-amphtml-binding attribute on elements with bindings.
 *
 * This is ported from the NodeJS optimizer.
 *
 * NodeJS:
 *
 * @version 4aa99eb6e16a39bb562acb67efdfd3ee3d993a98
 * @link https://github.com/ampproject/amp-toolbox/blob/4aa99eb6e16a39bb562acb67efdfd3ee3d993a98/packages/optimizer/lib/transformers/OptimizeAmpBind.js
 *
 * @package ampproject/amp-toolbox
 */
final class OptimizeAmpBind implements Transformer
{
    /**
     * Configuration store to use.
     *
     * @var TransformerConfiguration
     */
    private $configuration;

    /**
     * Instantiate an OptimizeAmpBind object.
     *
     * @param TransformerConfiguration $configuration Configuration store to use.
     */
    public function __construct(TransformerConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        if ($this->configuration->get(OptimizeAmpBindConfiguration::ENABLED) === false) {
            return;
        }

        if (!$this->hasAmpBindScriptElement($document)) {
            return;
        }

        $document->html->addBooleanAttribute(Attribute::I_AMPHTML_BINDING);

        for ($node = $document->html; $node !== null; $node = NodeWalker::nextNode($node)) {
            if (!$node instanceof Element) {
                continue;
            }

            if (Amp::isTemplate($node)) {
                $node = NodeWalker::skipNodeAndChildren($node);
                continue;
            }

            /** @var DOMAttr $attribute */
            foreach ($node->attributes as $attribute) {
                if (strpos($attribute->name, Amp::BIND_DATA_ATTR_PREFIX) === 0) {
                    $node->addBooleanAttribute(Attribute::I_AMPHTML_BINDING);
                    break;
                }
            }
        }
    }

    /**
     * Check whether the document has an amp-bind script element.
     *
     * @param Document $document Document to check.
     * @return bool Whether the document has an amp-bind script element.
     */
    private function hasAmpBindScriptElement(Document $document)
    {
        for ($element = $document->head->firstChild; $element !== null; $element = $element->nextSibling) {
            if (!$element instanceof Element) {
                continue;
            }

            if ($element->tagName !== Tag::SCRIPT) {
                continue;
            }

            if ($element->getAttribute(Attribute::CUSTOM_ELEMENT) !== Extension::BIND) {
                continue;
            }

            return true;
        }

        return false;
    }
}
