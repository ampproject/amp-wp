<?php

namespace AmpProject\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid tag ID is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidDocumentFilter extends InvalidArgumentException implements AmpException
{
    /**
     * Instantiate an InvalidDocumentFilter exception for a class that was not a valid filter.
     *
     * @param mixed $filter Filter that was registered.
     * @return self
     */
    public static function forFilter($filter)
    {
        $type = is_object($filter) ? get_class($filter) : gettype($filter);

        $message = is_string($filter)
            ? "Invalid document filter '{$filter}' was registered with the AmpProject\Dom\Document class."
            : ("Invalid document filter of type '{$type}' was registered with the AmpProject\Dom\Document class, '
                . 'expected AmpProject\Dom\Document\Filter.");

        return new self($message);
    }
}
