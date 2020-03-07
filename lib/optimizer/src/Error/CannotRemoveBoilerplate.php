<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;
use DOMElement;

final class CannotRemoveBoilerplate implements Error
{
    use ErrorProperties;

    const ATTRIBUTES_STRING             = 'Cannot remove boilerplate as either heights, media or sizes attribute is set: ';
    const RENDER_DELAYING_SCRIPT_STRING = 'Cannot remove boilerplate because the document contains a render-delaying extension: ';
    const AMP_AUDIO_STRING              = 'Cannot remove boilerplate because the document contains an extension that needs to know the dimensions of the browser: ';
    const UNSUPPORTED_LAYOUT_STRING     = 'Cannot remove boilerplate because of an unsupported layout: ';

    /**
     * Instantiate a CannotRemoveBoilerplate object for attributes that require the boilerplate to be around.
     *
     * @param DOMElement $element Element that contains the attributes that need the boilerplate.
     * @return self
     */
    public static function fromAttributesRequiringBoilerplate(DOMElement $element)
    {
        return new self(self::ATTRIBUTES_STRING . new ElementDump($element));
    }

    /**
     * Instantiate a CannotRemoveBoilerplate object for an amp-experiment element.
     *
     * @param DOMElement $element amp-experiment element.
     * @return self
     */
    public static function fromAmpExperiment(DOMElement $element)
    {
        return new self(self::RENDER_DELAYING_SCRIPT_STRING . $element->tagName);
    }

    /**
     * Instantiate a CannotRemoveBoilerplate object for an amp-audio element.
     *
     * @param DOMElement $element amp-audio element.
     * @return self
     */
    public static function fromAmpAudio(DOMElement $element)
    {
        return new self(self::AMP_AUDIO_STRING . new ElementDump($element));
    }

    /**
     * Instantiate a CannotRemoveBoilerplate object for an element with an unsupported layout.
     *
     * @param DOMElement $element Element with an unsupported layout.
     * @return self
     */
    public static function fromUnsupportedLayout(DOMElement $element)
    {
        return new self(self::UNSUPPORTED_LAYOUT_STRING . new ElementDump($element));
    }

    /**
     * Instantiate a CannotRemoveBoilerplate object for render-delaying script element.
     *
     * @param DOMElement $element Element with an unsupported layout.
     * @return self
     */
    public static function fromRenderDelayingScript(DOMElement $element)
    {
        $elementName = $element->hasAttribute('custom-element')
            ? $element->getAttribute('custom-element')
            : '<unknown>';

        return new self(self::UNSUPPORTED_LAYOUT_STRING . $elementName);
    }
}
