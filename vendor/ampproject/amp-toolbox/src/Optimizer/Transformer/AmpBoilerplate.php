<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Tag;

/**
 * Transformer that removes AMP boilerplate <style> and <noscript> tags in <head>, keeping only the amp-custom <style>
 * tag. It then (re-)inserts the amp-boilerplate unless the document is marked with the i-amphtml-no-boilerplate
 * attribute.
 *
 * This is ported from the Go optimizer.
 *
 * Go:
 * @version c9993b8ac4d17d1f05d3a1289956dadf3f9c370a
 * @link    https://github.com/ampproject/amppackager/blob/c9993b8ac4d17d1f05d3a1289956dadf3f9c370a/transformer/transformers/ampboilerplate.go
 *
 * @package ampproject/amp-toolbox
 */
final class AmpBoilerplate implements Transformer
{

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        $this->removeStyleAndNoscriptTags($document);

        if ($this->hasNoBoilerplateAttribute($document)) {
            return;
        }

        list($boilerplate, $css) = $this->determineBoilerplateAndCss($document->html);

        $styleNode = $document->createElement(Tag::STYLE);
        $styleNode->setAttribute($boilerplate, '');
        $document->head->appendChild($styleNode);

        $cssNode = $document->createTextNode($css);
        $styleNode->appendChild($cssNode);

        if ($boilerplate !== Attribute::AMP_BOILERPLATE) {
            return;
        }

        // Regular AMP boilerplate also includes a <noscript> element.
        $noscriptNode = $document->createElement(Tag::NOSCRIPT);
        $document->head->appendChild($noscriptNode);

        $noscriptStyleNode = $document->createElement(Tag::STYLE);
        $noscriptStyleNode->setAttribute($boilerplate, '');
        $noscriptNode->appendChild($noscriptStyleNode);

        $noscriptCssNode = $document->createTextNode(Amp::BOILERPLATE_NOSCRIPT_CSS);
        $noscriptStyleNode->appendChild($noscriptCssNode);
    }

    /**
     * Remove all <style> and <noscript> tags which are for the boilerplate.
     *
     * @param Document $document Document to remove the tags from.
     */
    private function removeStyleAndNoscriptTags(Document $document)
    {
        /**
         * Style element.
         *
         * @var Element $style
         */
        foreach (iterator_to_array($document->head->getElementsByTagName(Tag::STYLE)) as $style) {
            if (! $this->isBoilerplateStyle($style)) {
                continue;
            }
            if (Tag::NOSCRIPT === $style->parentNode->nodeName) {
                $style->parentNode->parentNode->removeChild($style->parentNode);
            } else {
                $style->parentNode->removeChild($style);
            }
        }
    }

    /**
     * Check whether an element is a boilerplate style.
     *
     * @param Element $element Element to check.
     * @return bool Whether the element is a boilerplate style.
     */
    private function isBoilerplateStyle(Element $element)
    {
        foreach (Attribute::ALL_BOILERPLATES as $boilerplate) {
            if ($element->hasAttribute($boilerplate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether it was already determined the boilerplate should be removed.
     *
     * We want to ensure we don't apply re-add the boilerplate again if it was already removed via SSR.
     *
     * @param Document $document DOM document to check for the attribute.
     * @return bool Whether it was determined that the boilerplate should be removed.
     */
    private function hasNoBoilerplateAttribute(Document $document)
    {
        if ($document->html->hasAttribute(Amp::NO_BOILERPLATE_ATTRIBUTE)) {
            return true;
        }

        return false;
    }

    /**
     * Determine and return the boilerplate attribute and inline CSS to use.
     *
     * @param Element $htmlElement HTML DOM element to check against.
     * @return array Tuple containing the $boilerplate and $css to use.
     */
    private function determineBoilerplateAndCss(Element $htmlElement)
    {
        $boilerplate = Attribute::AMP_BOILERPLATE;
        $css         = Amp::BOILERPLATE_CSS;

        foreach (Attribute::ALL_AMP4ADS as $attribute) {
            if (
                $htmlElement->hasAttribute($attribute)
                || (
                    $htmlElement->getAttribute(Document::EMOJI_AMP_ATTRIBUTE_PLACEHOLDER) === str_replace(
                        Attribute::AMP_EMOJI,
                        '',
                        $attribute
                    )
                )
            ) {
                $boilerplate = Attribute::AMP4ADS_BOILERPLATE;
                $css         = Amp::AMP4ADS_AND_AMP4EMAIL_BOILERPLATE_CSS;
            }
        }

        foreach (Attribute::ALL_AMP4EMAIL as $attribute) {
            if (
                $htmlElement->hasAttribute($attribute)
                || (
                    $htmlElement->getAttribute(Document::EMOJI_AMP_ATTRIBUTE_PLACEHOLDER) === str_replace(
                        Attribute::AMP_EMOJI,
                        '',
                        $attribute
                    )
                )
            ) {
                $boilerplate = Attribute::AMP4EMAIL_BOILERPLATE;
                $css         = Amp::AMP4ADS_AND_AMP4EMAIL_BOILERPLATE_CSS;
            }
        }

        return [$boilerplate, $css];
    }
}
