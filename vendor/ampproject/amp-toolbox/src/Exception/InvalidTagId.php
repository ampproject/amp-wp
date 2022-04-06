<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid tag ID is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidTagId extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidTagId exception for a tag that is not found within the tag name index.
     *
     * @param string $tagId Spec name that was requested.
     * @return self
     */
    public static function forTagId($tagId)
    {
        $message = "Invalid tag ID '{$tagId}' was requested from the validator tag.";

        return new self($message);
    }
}
