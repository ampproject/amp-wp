<?php

namespace AmpProject\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when a URL could not be parsed.
 *
 * @package ampproject/amp-toolbox
 */
final class FailedToParseUrl extends InvalidArgumentException implements AmpException
{

    /**
     * Instantiate a FailedToParseUrl exception for a URL that could not be parsed.
     *
     * @param string $url URL that failed to be parsed.
     * @return self
     */
    public static function forUrl($url)
    {
        $message = "Failed to parse the URL '{$url}'.";

        return new self($message);
    }
}
