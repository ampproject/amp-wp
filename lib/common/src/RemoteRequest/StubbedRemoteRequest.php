<?php

namespace Amp\RemoteRequest;

use Amp\Exception\FailedToFetchFromRemoteUrl;
use Amp\RemoteRequest;
use LogicException;

/**
 * Stub for simulating remote requests.
 *
 * @package amp/common
 */
final class StubbedRemoteRequest implements RemoteRequest
{

    /**
     * Associative array of data for mapping between arguments and returned results.
     *
     * @var array
     */
    private $argumentMap;

    /**
     * Instantiate a StubbedRemoteRequest object.
     *
     * @param array $argumentMap Associative array of data for mapping between arguments and returned results.
     */
    public function __construct($argumentMap)
    {
        $this->argumentMap = $argumentMap;
    }

    /**
     * Fetch the contents of a remote request.
     *
     * @param string $url URL to fetch.
     * @return string Contents retrieved from the remote URL.
     * @throws FailedToFetchFromRemoteUrl If fetching the contents from the URL failed.
     */
    public function fetch($url)
    {
        if (! array_key_exists($url, $this->argumentMap)) {
            throw new LogicException("Trying to stub a remote request for an unknown URL: {$url}.");
        }

        return $this->argumentMap[$url];
    }
}
