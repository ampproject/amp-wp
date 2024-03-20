<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid tag name is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidTagName extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidTagName exception for a tag that is not found within the tag name index.
     *
     * @param string $tagName Tag name that was requested.
     * @return self
     */
    public static function forTagName($tagName)
    {
        $message = "Invalid tag name '{$tagName}' was requested from the validator spec.";

        return new self($message);
    }
}
