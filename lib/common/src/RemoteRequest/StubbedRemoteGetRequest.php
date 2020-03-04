<?php

namespace Amp\RemoteRequest;

use Amp\Exception\FailedToGetFromRemoteUrl;
use Amp\RemoteGetRequest;
use LogicException;

/**
 * Stub for simulating remote requests.
 *
 * @package amp/common
 */
final class StubbedRemoteGetRequest implements RemoteGetRequest
{

    /**
     * Associative array of data for mapping between arguments and returned results.
     *
     * @var array
     */
    private $argumentMap;

    /**
     * Instantiate a StubbedRemoteGetRequest object.
     *
     * @param array $argumentMap Associative array of data for mapping between arguments and returned results.
     */
    public function __construct($argumentMap)
    {
        $this->argumentMap = $argumentMap;
    }

    /**
     * Do a GET request to retrieve the contents of a remote URL.
     *
     * @param string $url URL to get.
     * @return string Contents retrieved from the remote URL.
     * @throws FailedToGetFromRemoteUrl If retrieving the contents from the URL failed.
     */
    public function get($url)
    {
        if (! array_key_exists($url, $this->argumentMap)) {
            throw new LogicException("Trying to stub a remote request for an unknown URL: {$url}.");
        }

        return $this->argumentMap[$url];
    }
}
