<?php

namespace AmpProject\Exception;

use Exception;
use RuntimeException;

/**
 * Exception thrown when a remote request failed.
 *
 * @package ampproject/common
 */
final class FailedToGetFromRemoteUrl extends RuntimeException implements FailedRemoteRequest
{

    /**
     * Status code of the failed request.
     *
     * This is not always set.
     *
     * @var int
     */
    private $statusCode;

    /**
     * Instantiate a FailedToGetFromRemoteUrl exception for a URL if an HTTP status code is available.
     *
     * @param string $url    URL that failed to be fetched.
     * @param int    $status HTTP Status that was returned.
     * @return self
     */
    public static function withHttpStatus($url, $status)
    {
        $message = "Failed to fetch the contents from the URL '{$url}' as it returned HTTP status {$status}.";

        $exception = new self($message);
        $exception->statusCode = $status;

        return $exception;
    }

    /**
     * Instantiate a FailedToGetFromRemoteUrl exception for a URL if an HTTP status code is not available.
     *
     * @param string $url URL that failed to be fetched.
     * @return self
     */
    public static function withoutHttpStatus($url)
    {
        $message = "Failed to fetch the contents from the URL '{$url}'.";

        return new self($message);
    }

    /**
     * Instantiate a FailedToGetFromRemoteUrl exception for a URL if an exception was thrown.
     *
     * @param string    $url       URL that failed to be fetched.
     * @param Exception $exception Exception that was thrown.
     * @return self
     */
    public static function withException($url, Exception $exception)
    {
        $message = "Failed to fetch the contents from the URL '{$url}': {$exception->getMessage()}.";

        return new self($message, null, $exception);
    }

    /**
     * Check whether the status code is set for this exception.
     *
     * @return bool
     */
    public function hasStatusCode()
    {
        return isset($this->statusCode);
    }

    /**
     * Get the HTTP status code associated with this exception.
     *
     * Returns -1 if no status code was provided.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->hasStatusCode() ? $this->statusCode : -1;
    }
}
