<?php

namespace AmpProject\Exception;

use AmpProject\Dom\Element;
use AmpProject\Dom\ElementDump;
use OverflowException;

/**
 * Exception thrown when the maximum CSS byte count has been exceeded.
 *
 * @package ampproject/amp-toolbox
 */
final class MaxCssByteCountExceeded extends OverflowException implements AmpException
{
    /**
     * Instantiate a MaxCssByteCountExceeded exception for an inline style that exceeds the maximum byte count.
     *
     * @param Element $element Element that was supposed to receive the inline style.
     * @param string  $style   Inline style that was supposed to be added.
     * @return self
     */
    public static function forInlineStyle(Element $element, $style)
    {
        $message = "Maximum allowed CSS byte count exceeded for inline style '{$style}': " . new ElementDump($element);

        return new self($message);
    }

    /**
     * Instantiate a MaxCssByteCountExceeded exception for an amp-custom style that exceeds the maximum byte count.
     *
     * @param string $style Amp-custom style that was supposed to be added.
     * @return self
     */
    public static function forAmpCustom($style)
    {
        $message = "Maximum allowed CSS byte count exceeded for amp-custom style '{$style}'";

        return new self($message);
    }
}
