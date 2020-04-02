<?php

namespace AmpProject\Exception;

use RuntimeException;

/**
 * Exception thrown when a cached remote response could not be retrieved.
 *
 * @package ampproject/common
 */
final class FailedToGetCachedResponseData extends RuntimeException implements AmpException
{

    /**
     * Instantiate a FailedToGetCachedResponseData exception for a URL if the cached response data could not be retrieved.
     *
     * @param string $url URL that failed to be fetched.
     * @return self
     */
    public static function withUrl($url)
    {
        $message = "Failed to retrieve the cached response data for the URL '{$url}'.";

        return new self($message);
    }
}
