<?php

namespace AmpProject\Exception;

use LogicException;

/**
 * Exception thrown when a required DOM element could not be retrieved from the document.
 *
 * @package ampproject/amp-toolbox
 */
final class FailedToRetrieveRequiredDomElement extends LogicException implements AmpException
{
    /**
     * Instantiate a FailedToRetrieveRequiredDomElement exception for the <html> DOM element.
     *
     * @param mixed $retrievedElement What was returned when trying to retrieve the element.
     * @return FailedToRetrieveRequiredDomElement
     */
    public static function forHtmlElement($retrievedElement)
    {
        $type    = is_object($retrievedElement) ? get_class($retrievedElement) : gettype($retrievedElement);
        $message = "Failed to retrieve required <html> DOM element, got '{$type}' instead.";

        return new self($message);
    }

    /**
     * Instantiate a FailedToRetrieveRequiredDomElement exception for the <head> DOM element.
     *
     * @param mixed $retrievedElement What was returned when trying to retrieve the element.
     * @return FailedToRetrieveRequiredDomElement
     */
    public static function forHeadElement($retrievedElement)
    {
        $type    = is_object($retrievedElement) ? get_class($retrievedElement) : gettype($retrievedElement);
        $message = "Failed to retrieve required <head> DOM element, got '{$type}' instead.";

        return new self($message);
    }

    /**
     * Instantiate a FailedToRetrieveRequiredDomElement exception for the <body> DOM element.
     *
     * @param mixed $retrievedElement What was returned when trying to retrieve the element.
     * @return FailedToRetrieveRequiredDomElement
     */
    public static function forBodyElement($retrievedElement)
    {
        $type    = is_object($retrievedElement) ? get_class($retrievedElement) : gettype($retrievedElement);
        $message = "Failed to retrieve required <body> DOM element, got '{$type}' instead.";

        return new self($message);
    }
}
