<?php

namespace Amp\Optimizer\Transformer;

use Amp\AmpWP\Dom\Document;
use Amp\Optimizer\Error;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Transformer;
use DOMElement;

final class ServerSideRendering implements Transformer
{

    const LAYOUT_ATTRIBUTE         = 'i-amphtml-layout';
    const NO_BOILERPLATE_ATTRIBUTE = 'i-amphtml-no-boilerplate';

    const RENDER_DELAYING_EXTENSIONS = [
        'amp-dynamic-css-classes',
        'amp-experiment',
    ];

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        if ($this->isAlreadyTransformed($document)) {
            return;
        }

        /*
         * Within the loop we apply the layout to the custom tags (amp-foo...) where possible, but while we're at this
         * we also look for reasons not to remove the boilerplate.
         */
        $canRemoveBoilerplate = true;
        foreach ($document->amp_elements as $amp_element) {
            /*
             * If these attributes are used on any AMP custom element tags within the document, we can't remove the
             * boilerplate - they require the boilerplate.
             */
            if ($amp_element->hasAttribute('heights') || $amp_element->hasAttribute('media') || $amp_element->hasAttribute('sizes')) {
                $errors->add(Error\CannotRemoveBoilerplate::from_attributes_requiring_boilerplate($amp_element));
                $canRemoveBoilerplate = false;
            }

            /*
             * amp-experiment is a render delaying extension iff the tag is used in the doc. We check for that here
             * rather than checking for the existence of the amp-experiment script in IsRenderDelayingExtension below.
             */
            if ($amp_element->tagName === 'amp-experiment') {
                $errors->add(Error\CannotRemoveBoilerplate::from_amp_experiment($amp_element));
                $canRemoveBoilerplate = false;
            }

            /*
             * amp-audio requires knowing the dimensions of the browser. Do not remove the boilerplate or apply layout
             * if amp-audio is present in the document.
             */
            if ($amp_element->tagName === 'amp-audio') {
                $errors->add(Error\CannotRemoveBoilerplate::from_amp_audio($amp_element));
                $canRemoveBoilerplate = false;
                continue;
            }

            /*
             * Now apply the layout to the custom elements. If we encounter any unsupported layout, the applyLayout()
             * method returns false and we can't remove the boilerplate.
             */
            if (! $this->applyLayout($amp_element)) {
                $errors->add(Error\CannotRemoveBoilerplate::from_unsupported_layout($amp_element));
                $canRemoveBoilerplate = false;
            }
        }

        // Emit the amp-runtime marker to indicate that we're applying server side rendering in the document.
        $ampRuntimeMarker = $document->createElement('style');
        $ampRuntimeMarker->setAttribute('amp-runtime', '');
        $document->head->insertBefore($ampRuntimeMarker, $document->head->firstChild);

        foreach ($document->xpath->query('.//script[ @custom-element ]', $document->head) as $customElementScript) {
            if ($this->isRenderDelayingExtension($customElementScript)) {
                $errors->add(Error\CannotRemoveBoilerplate::from_render_delaying_script($customElementScript));
                $canRemoveBoilerplate = false;
            }
        }

        /*
         * Below, we're only concerned about removing the boilerplate.
         * If we've already determined that we can't, we're done here.
         */
        if (! $canRemoveBoilerplate) {
            return;
        }

        // The boilerplate can be removed, note it on the <html> tag.
        $document->html->setAttribute(self::NO_BOILERPLATE_ATTRIBUTE, '');

        /*
         * Find the boilerplate and remove it.
         * The following code assumes that the <noscript> tag in the head is only ever used for boilerplate.
         */
        foreach ($document->xpath->query('.//noscript', $document->head) as $noscriptTagInHead) {
            /** @var DOMElement $noscriptTagInHead */
            $noscriptTagInHead->parentNode->removeChild($noscriptTagInHead);
        }

        foreach ($document->xpath->query('.//style[ @amp-boilerplate ]', $document->head) as $boilerplateStyleTag) {
            /** @var DOMElement $boilerplateStyleTag */
            $boilerplateStyleTag->parentNode->removeChild($boilerplateStyleTag);
        }
    }

    /**
     * Check whether the document was already transformed.
     *
     * We want to ensure we don't apply server-side rendering modifications more than once.
     *
     * @param Document $document DOM document to apply the transformations to.
     * @return bool Whether the document was already transformed.
     */
    private function isAlreadyTransformed(Document $document)
    {
        if ($document->html->hasAttribute(self::LAYOUT_ATTRIBUTE)) {
            return true;
        }

        // Mark the document as "already transformed".
        $document->html->setAttribute(self::LAYOUT_ATTRIBUTE, '');

        return false;
    }

    /**
     * Check whether a given element is a script for a render-delaying extension.
     *
     * @param DOMElement $element Element to check.
     * @return bool Whether the element is a script for a render-delaying extension.
     */
    private function isRenderDelayingExtension(DOMElement $element)
    {
        if ($element->tagName !== 'script') {
            return false;
        }

        if (! $element->hasAttribute('custom-element')) {
            return false;
        }

        $customElement = $element->getAttribute('custom-element');

        return in_array($customElement, self::RENDER_DELAYING_EXTENSIONS, true);
    }

    /**
     * Apply the adequate layout to a custom element.
     *
     * @param DOMElement $element Element to apply the layout to.
     * @return boolean Whether applying the layout was successful or not.
     */
    private function applyLayout(DOMElement $element)
    {
        // @todo
        return true;
    }
}
