<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Dom\Element;
use AmpProject\Dom\ElementDump;
use AmpProject\Optimizer\Error;
use Exception;

/**
 * Optimizer error object for when the AMP boilerplate style cannot be removed.
 *
 * @package ampproject/amp-toolbox
 */
final class CannotRemoveBoilerplate implements Error
{
    use ErrorProperties;

    const ATTRIBUTES_STRING             = 'Cannot remove boilerplate as either heights, media or sizes attribute is '
                                          . 'set and cannot be adapted: ';
    const ATTRIBUTES_EXCEPTION_STRING   = 'Cannot remove boilerplate as the removal of either heights, media or sizes '
                                          . 'attribute produced an error: ';
    const RENDER_DELAYING_SCRIPT_STRING = 'Cannot remove boilerplate because the document contains a render-delaying '
                                          . 'extension: ';
    const AMP_AUDIO_STRING              = 'Cannot remove boilerplate because the document contains an extension that '
                                          . 'needs to know the dimensions of the browser: ';
    const UNSUPPORTED_LAYOUT_STRING     = 'Cannot remove boilerplate because of an unsupported layout: ';

    /**
     * Instantiate a CannotRemoveBoilerplate object for attributes that require the boilerplate to be around.
     *
     * @param Element $element Element that contains the attributes that need the boilerplate.
     * @return self
     */
    public static function fromAttributesRequiringBoilerplate(Element $element)
    {
        return new self(self::ATTRIBUTES_STRING . new ElementDump($element));
    }

    /**
     * Instantiate a CannotRemoveBoilerplate object for attributes that require the boilerplate to be around.
     *
     * @param Exception $exception Exception being thrown.
     * @return self
     */
    public static function fromAttributeThrowingException($exception)
    {
        return new self(self::ATTRIBUTES_EXCEPTION_STRING . $exception->getMessage());
    }

    /**
     * Instantiate a CannotRemoveBoilerplate object for an amp-experiment element.
     *
     * @param Element $element amp-experiment element.
     * @return self
     */
    public static function fromAmpExperiment(Element $element)
    {
        return new self(self::RENDER_DELAYING_SCRIPT_STRING . $element->tagName);
    }

    /**
     * Instantiate a CannotRemoveBoilerplate object for an amp-audio element.
     *
     * @param Element $element amp-audio element.
     * @return self
     */
    public static function fromAmpAudio(Element $element)
    {
        return new self(self::AMP_AUDIO_STRING . new ElementDump($element));
    }

    /**
     * Instantiate a CannotRemoveBoilerplate object for an element with an unsupported layout.
     *
     * @param Element $element Element with an unsupported layout.
     * @return self
     */
    public static function fromUnsupportedLayout(Element $element)
    {
        return new self(self::UNSUPPORTED_LAYOUT_STRING . new ElementDump($element));
    }

    /**
     * Instantiate a CannotRemoveBoilerplate object for render-delaying script element.
     *
     * @param Element $element Element with an unsupported layout.
     * @return self
     */
    public static function fromRenderDelayingScript(Element $element)
    {
        $elementName = $element->hasAttribute('custom-element')
            ? $element->getAttribute('custom-element')
            : '<unknown>';

        return new self(self::UNSUPPORTED_LAYOUT_STRING . $elementName);
    }
}
