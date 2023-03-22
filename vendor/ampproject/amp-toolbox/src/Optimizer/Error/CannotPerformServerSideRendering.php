<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Dom\Element;
use AmpProject\Dom\ElementDump;
use AmpProject\Exception\MaxCssByteCountExceeded;
use AmpProject\Optimizer\Error;

/**
 * Optimizer error object for when server-side rendering cannot be performed.
 *
 * @package ampproject/amp-toolbox
 */
final class CannotPerformServerSideRendering implements Error
{
    use ErrorProperties;

    const INVALID_INPUT_WIDTH         = 'Cannot perform serverside rendering, invalid input width: ';
    const INVALID_INPUT_HEIGHT        = 'Cannot perform serverside rendering, invalid input height: ';
    const UNSUPPORTED_LAYOUT          = 'Cannot perform serverside rendering, unsupported layout: ';
    const EXCEEDED_MAX_CSS_BYTE_COUNT = 'Cannot perform serverside rendering, exceeded maximum CSS byte count: ';

    /**
     * Instantiate a CannotPerformServerSideRendering object for an element with an invalid input width.
     *
     * @param Element $element Element that has an invalid input width.
     * @return self
     */
    public static function fromInvalidInputWidth(Element $element)
    {
        return new self(self::INVALID_INPUT_WIDTH . new ElementDump($element));
    }

    /**
     * Instantiate a CannotPerformServerSideRendering object for an element with an invalid input height.
     *
     * @param Element $element Element that has an invalid input height.
     * @return self
     */
    public static function fromInvalidInputHeight(Element $element)
    {
        return new self(self::INVALID_INPUT_HEIGHT . new ElementDump($element));
    }

    /**
     * Instantiate a CannotPerformServerSideRendering object for an element with an invalid input height.
     *
     * @param Element $element Element that has an invalid input height.
     * @param string  $layout  Resulting layout.
     * @return self
     */
    public static function fromUnsupportedLayout(Element $element, $layout)
    {
        return new self(self::UNSUPPORTED_LAYOUT . new ElementDump($element) . " => {$layout}");
    }

    /**
     * Instantiate a CannotPerformServerSideRendering object for a MaxCssByteCountExceeded exception.
     *
     * @param MaxCssByteCountExceeded $exception Caught exception.
     * @param Element                 $element   Element that caused the exception.
     * @return self
     */
    public static function fromMaxCssByteCountExceededException(MaxCssByteCountExceeded $exception, Element $element)
    {
        return new self(
            self::EXCEEDED_MAX_CSS_BYTE_COUNT . new ElementDump($element) . " => {$exception->getMessage()}"
        );
    }
}
