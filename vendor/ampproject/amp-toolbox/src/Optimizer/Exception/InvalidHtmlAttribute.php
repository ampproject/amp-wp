<?php

namespace AmpProject\Optimizer\Exception;

use AmpProject\Dom\Element;
use AmpProject\Dom\ElementDump;
use DomainException;

/**
 * Exception thrown when an invalid HTML attribute was detected.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidHtmlAttribute extends DomainException implements AmpOptimizerException
{
    /**
     * Instantiate an InvalidHtmlAttribute exception for an invalid attribute value.
     *
     * @param string  $attributeName Name of the attribute.
     * @param Element $element       Element that contains the invalid attribute.
     * @return self
     */
    public static function fromAttribute($attributeName, Element $element)
    {
        $message = "Invalid value detected for attribute '{$attributeName}': " . new ElementDump($element);

        return new self($message);
    }
}
