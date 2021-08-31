<?php

namespace AmpProject\Exception;

use RuntimeException;

/**
 * Exception thrown when a link could not be created.
 *
 * @package ampproject/amp-toolbox
 */
final class FailedToCreateLink extends RuntimeException implements AmpException
{

    /**
     * Instantiate a FailedToCreateLink exception for a link that could not be created.
     *
     * @param mixed $link Link that was not as expected.
     * @return self
     */
    public static function forLink($link)
    {
        $type = is_object($link) ? get_class($link) : gettype($link);
        $message = "Failed to create a link via the link manager. "
                 . "Expected to produce an 'AmpProject\\Dom\\Element', got '$type' instead.";

        return new self($message);
    }
}
