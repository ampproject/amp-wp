<?php

namespace Amp\Optimizer\Transformer;

use Amp\AmpWP\Dom\Document;
use Amp\Optimizer\Layout;
use Amp\Optimizer\Error;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Transformer;
use AMP_CSS_Length;
use DOMElement;

final class ServerSideRendering implements Transformer
{

    const LAYOUT_ATTRIBUTE          = 'i-amphtml-layout';
    const NO_BOILERPLATE_ATTRIBUTE  = 'i-amphtml-no-boilerplate';
    const LAYOUT_CLASS_PREFIX       = 'i-amphtml-layout-';
    const LAYOUT_SIZE_DEFINED_CLASS = 'i-amphtml-layout-size-defined';
    const SIZER_ELEMENT             = 'i-amphtml-sizer';

    const RENDER_DELAYING_EXTENSIONS = [
        'amp-dynamic-css-classes',
        'amp-experiment',
    ];

    const SUPPORTED_LAYOUTS = [
        '',
        Layout::NODISPLAY,
        Layout::FIXED,
        Layout::FIXED_HEIGHT,
        Layout::RESPONSIVE,
        Layout::CONTAINER,
        Layout::FILL,
        Layout::FLEX_ITEM,
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

            // Skip tags inside a template tag.
            if ($this->hasAncestorWithTag($amp_element, 'template')) {
                continue;
            }

            /*
             * If these attributes are used on any AMP custom element tags within the document, we can't remove the
             * boilerplate - they require the boilerplate.
             */
            if ($amp_element->hasAttribute('heights') || $amp_element->hasAttribute('media') || $amp_element->hasAttribute('sizes')) {
                $errors->add(Error\CannotRemoveBoilerplate::fromAttributesRequiringBoilerplate($amp_element));
                $canRemoveBoilerplate = false;
            }

            /*
             * amp-experiment is a render delaying extension iff the tag is used in the doc. We check for that here
             * rather than checking for the existence of the amp-experiment script in IsRenderDelayingExtension below.
             */
            if ($amp_element->tagName === 'amp-experiment' && $this->isAmpExperimentUsed($amp_element)) {
                $errors->add(Error\CannotRemoveBoilerplate::fromAmpExperiment($amp_element));
                $canRemoveBoilerplate = false;
            }

            /*
             * amp-audio requires knowing the dimensions of the browser. Do not remove the boilerplate or apply layout
             * if amp-audio is present in the document.
             */
            if ($amp_element->tagName === 'amp-audio') {
                $errors->add(Error\CannotRemoveBoilerplate::fromAmpAudio($amp_element));
                $canRemoveBoilerplate = false;
                continue;
            }

            /*
             * Now apply the layout to the custom elements. If we encounter any unsupported layout, the applyLayout()
             * method returns false and we can't remove the boilerplate.
             */
            if (! $this->applyLayout($document, $amp_element, $errors)) {
                $errors->add(Error\CannotRemoveBoilerplate::fromUnsupportedLayout($amp_element));
                $canRemoveBoilerplate = false;
            }
        }

        foreach ($document->xpath->query('.//script[ @custom-element ]', $document->head) as $customElementScript) {
            if ($this->isRenderDelayingExtension($customElementScript)) {
                $errors->add(Error\CannotRemoveBoilerplate::fromRenderDelayingScript($customElementScript));
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

        foreach ($document->xpath->query('.//style[ @amp-boilerplate or @amp4ads-boilerplate or @amp4email-boilerplate ]', $document->head) as $boilerplateStyleTag) {
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
     * @param DOMElement      $element  Element to apply the layout to.
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return boolean Whether applying the layout was successful or not.
     */
    private function applyLayout(Document $document, DOMElement $element, ErrorCollection $errors)
    {
        // @todo Remove dependency on plugin's AMP_CSS_Length objects here.
        $ampLayout = $this->parseLayout($element->getAttribute('layout'));

        $inputWidth = new AMP_CSS_Length($element->getAttribute('width'));
        $inputWidth->validate(/* $allow_auto */ true, /* $allow_fluid */ false);
        if (! $inputWidth->is_valid()) {
            $errors->add(Error\CannotPerformServerSideRendering::fromInvalidInputWidth($element));
            return false;
        }

        $inputHeight = new AMP_CSS_Length($element->getAttribute('height'));
        $inputHeight->validate(/* $allow_auto */ true, /* $allow_fluid */ $ampLayout === Layout::FLUID);
        if (! $inputHeight->is_valid()) {
            $errors->add(Error\CannotPerformServerSideRendering::fromInvalidInputHeight($element));
            return false;
        }

        // Calculate effective width, height and layout.
        $width  = $this->calculateWidth($ampLayout, $inputWidth, $element->tagName);
        $height = $this->calculateHeight($ampLayout, $inputHeight, $element->tagName);
        $layout = $this->calculateLayout($ampLayout, $width, $height, $element->getAttribute('sizes'), $element->getAttribute('heights'));

        if (! $this->isSupportedLayout($layout)) {
            $errors->add(Error\CannotPerformServerSideRendering::fromUnsupportedLayout($element, $layout));
            return false;
        }

        $this->applyLayoutAttributes($element, $layout, $width, $height);
        $this->maybeAddSizerInto($document, $element, $layout, $width, $height);

        return true;
    }

    /**
     * Parse the layout attribute value.
     *
     * @param string $layout Layout attribute value.
     * @return string Validated AMP layout, or empty string if none.
     */
    private function parseLayout($layout)
    {
        if (empty($layout)) {
            return '';
        }

        $layout = strtolower($layout);

        if (in_array($layout, Layout::VALID_LAYOUTS, true)) {
            return $layout;
        }

        return '';
    }

    /**
     * Calculate the width of an element for its requested layout.
     *
     * @param string         $inputLayout Requested layout.
     * @param AMP_CSS_Length $inputWidth  Input value for the width.
     * @param string         $tagName     Tag name of the element.
     * @return AMP_CSS_Length Calculated Width.
     */
    private function calculateWidth($inputLayout, AMP_CSS_Length $inputWidth, $tagName)
    {
        if ((empty($inputLayout) || $inputLayout === Layout::FIXED) && ! $inputWidth->is_set()) {
            // These values come from AMP's runtime and can be found in
            // https://github.com/ampproject/amphtml/blob/master/src/layout.js#L70
            switch ($tagName) {
                case 'amp-analytics':
                case 'amp-pixel':
                    $width = new AMP_CSS_Length('1px');
                    $width->validate(/* $allow_auto */ false, /* $allow_fluid */ false);
                    return $width;
                case 'amp-audio':
                    $width = new AMP_CSS_Length('auto');
                    $width->validate(/* $allow_auto */ true, /* $allow_fluid */ false);
                    return $width;
                case 'amp-social-share':
                    $width = new AMP_CSS_Length('60px');
                    $width->validate(/* $allow_auto */ false, /* $allow_fluid */ false);
                    return $width;
            }
        }

        return $inputWidth;
    }

    /**
     * Calculate the height of an element for its requested layout.
     *
     * @param string         $inputLayout Requested layout.
     * @param AMP_CSS_Length $inputHeight Input value for the height.
     * @param string         $tagName     Tag name of the element.
     * @return AMP_CSS_Length Calculated Height.
     */
    private function calculateHeight($inputLayout, AMP_CSS_Length $inputHeight, $tagName)
    {
        if ((empty($inputLayout) || $inputLayout === Layout::FIXED || $inputLayout === Layout::FIXED_HEIGHT) && ! $inputHeight->is_set()) {
            // These values come from AMP's runtime and can be found in
            // https://github.com/ampproject/amphtml/blob/master/src/layout.js#L70
            switch ($tagName) {
                case 'amp-analytics':
                case 'amp-pixel':
                    $height = new AMP_CSS_Length('1px');
                    $height->validate(/* $allow_auto */ false, /* $allow_fluid */ false);
                    return $height;
                case 'amp-audio':
                    $height = new AMP_CSS_Length('auto');
                    $height->validate(/* $allow_auto */ true, /* $allow_fluid */ false);
                    return $height;
                case 'amp-social-share':
                    $height = new AMP_CSS_Length('44px');
                    $height->validate(/* $allow_auto */ false, /* $allow_fluid */ false);
                    return $height;
            }
        }

        return $inputHeight;
    }

    /**
     * Calculate the final AMP layout attribute for an element.
     *
     * @param string         $inputLayout Requested layout.
     * @param AMP_CSS_Length $width       Calculated width.
     * @param AMP_CSS_Length $height      Calculated height.
     * @param string         $sizesAttr   Sizes attribute value.
     * @param string         $heightsAttr Heights attribute value.
     * @return string Calculated layout.
     */
    private function calculateLayout(
        $inputLayout,
        AMP_CSS_Length $width,
        AMP_CSS_Length $height,
        $sizesAttr,
        $heightsAttr
    ) {
        if (! empty($inputLayout)) {
            return $inputLayout;
        }

        if (! $width->is_set() && ! $height->is_set()) {
            return Layout::CONTAINER;
        }

        if ($height->is_set() && (! $width->is_set() || $width->is_auto())) {
            return Layout::FIXED_HEIGHT;
        }

        if ($height->is_set() && $width->is_set() && (! empty($sizesAttr) || ! empty($heightsAttr))) {
            return Layout::RESPONSIVE;
        }

        return Layout::FIXED;
    }

    /**
     * Check whether a layout is support for SSR.
     *
     * @param string $layout Layout to check.
     * @return bool Whether the layout is supported for SSR.
     */
    private function isSupportedLayout($layout)
    {
        return in_array($layout, self::SUPPORTED_LAYOUTS, true);
    }

    /**
     * Apply the calculated layout attributes to an element.
     *
     * @param DOMElement     $element Element to apply the layout attributes to.
     * @param string         $layout  Final layout.
     * @param AMP_CSS_Length $width   Calculated width.
     * @param AMP_CSS_Length $height  Calculated height.
     */
    private function applyLayoutAttributes(DOMElement $element, $layout, AMP_CSS_Length $width, AMP_CSS_Length $height)
    {
        $this->addClass($element, $this->getLayoutClass($layout));

        if ($this->isLayoutSizeDefined($layout)) {
            $this->addClass($element, self::LAYOUT_SIZE_DEFINED_CLASS);
        }

        $styles = '';
        switch ($layout) {
            case Layout::NODISPLAY:
                $element->setAttribute('hidden', 'hidden');
                break;
            case Layout::FIXED:
                $styles = "width:{$width->get_numeral()}{$width->get_unit()};height:{$height->get_numeral()}{$height->get_unit()};";
                break;
            case Layout::FIXED_HEIGHT:
                $styles = "height:{$height->get_numeral()}{$height->get_unit()};";
                break;
            case Layout::RESPONSIVE:
                // Do nothing here but emit <i-amphtml-sizer> later.
                break;
            case Layout::FILL:
            case Layout::CONTAINER:
                // Do nothing here.
                break;
            case Layout::FLEX_ITEM:
                if ($width->is_set()) {
                    $styles = "width:{$width->get_numeral()}{$width->get_unit()};";
                }
                if ($height->is_set()) {
                    $styles .= "height:{$height->get_numeral()}{$height->get_unit()};";
                }
                break;
        }

        // We prepend just in case an existing value (which shouldn't be there for valid docs) doesn't end with ';'.
        if ($element->hasAttribute('style')) {
            $styles .= $element->getAttribute('style');
        }
        if (! empty($styles)) {
            $element->setAttribute('style', $styles);
        }

        $element->setAttribute(self::LAYOUT_ATTRIBUTE, $layout);
    }

    /**
     * Get the class to use for a given layout.
     *
     * @param string $layout Layout to get the class for.
     * @return string Class name to use for the layout.
     */
    private function getLayoutClass($layout)
    {
        if (empty($layout)) {
            return '';
        }

        return self::LAYOUT_CLASS_PREFIX . $layout;
    }

    /**
     * Add a class to an element.
     *
     * This makes sure we keep existing classes on the element.
     *
     * @param DOMElement $element Element to add a class to.
     * @param string     $class   Class to add.
     */
    private function addClass(DOMElement $element, $class)
    {
        if ($element->hasAttribute('class') && ! empty($element->getAttribute('class'))) {
            $class = "{$element->getAttribute('class')} {$class}";
        }

        $element->setAttribute('class', $class);
    }

    /**
     * Check whether the provided layout is a layout with a defined size.
     *
     * @param string $layout Layout to check.
     * @return bool Whether the layout has a defined size.
     */
    private function isLayoutSizeDefined($layout)
    {
        return in_array($layout, Layout::SIZE_DEFINED_LAYOUTS, true);
    }

    private function maybeAddSizerInto(
        Document $document,
        DOMElement $element,
        $layout,
        AMP_CSS_Length $width,
        AMP_CSS_Length $height
    ) {
        if ($layout !== Layout::RESPONSIVE
            || ! $width->is_set()
            || $width->get_numeral() === 0
            || ! $height->is_set()
            || $width->get_unit() !== $height->get_unit()) {
            return;
        }

        $padding = $height->get_numeral() / $width->get_numeral() * 100;
        $sizer   = $document->createElement(self::SIZER_ELEMENT);
        $sizer->setAttribute('style', sprintf('display:block;padding-top:%1.4F%%;', $padding));
        $element->insertBefore($sizer, $element->firstChild);
    }

    /**
     * Check whether the element has an ancestor of a given tag type.
     *
     * @param DOMElement $element Element to check the ancestor tree of.
     * @param string     $tagName Name of the tag to look for.
     * @return bool Whether the element has an ancestor of the given tag name.
     */
    private function hasAncestorWithTag(DOMElement $element, $tagName)
    {
        $parent = $element->parentNode;
        while ($parent !== null) {
            if ($parent instanceof DOMElement && $parent->tagName === $tagName) {
                return true;
            }
            $parent = $parent->parentNode;
        }
        return false;
    }

    /**
     * Check if the amp-experiment element is actually used.
     *
     * This checks if amp-experiment has one child that is a script/json tag with a textnode that is parsable JSON and
     * not empty. The validator ensures that the script/json is parsable but since transformers may be used outside of
     * validation it is checked here as well.
     *
     * @param DOMElement $element Element to check,
     * @return bool Whether the amp-experiment element is actually used.
     */
    private function isAmpExperimentUsed(DOMElement $element)
    {
        $script = null;
        $child  = $element->firstChild;

        while ($child) {
            if ($child->tagName === 'script' && strtolower($child->getAttribute('type')) === 'application/json') {
                $script = $child;
                break;
            }
            $child = $child->nextSibling;
        }

        // If not script/json tag, then not used.
        if ($script === null) {
            return false;
        }

        // If not exactly one child is present, then not used.
        if ($script->childNodes->length !== 1) {
            return false;
        }

        $child = $script->firstChild;

        // If child is not a text node or CDATA section, then not used.
        if ($child->nodeType !== XML_TEXT_NODE && $child->nodeType !== XML_CDATA_SECTION_NODE) {
            return false;
        }

        $json = $child->textContent;

        // If textnode is not JSON parsable, then not used.
        $experiment = json_decode($json, /*$assoc*/ true);
        if ($experiment === null) {
            return false;
        }

        // If JSON is empty, then not used.
        if (count($experiment) === 0) {
            return false;
        }

        // Otherwise, used.
        return true;
    }
}
