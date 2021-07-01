<?php

namespace AmpProject\RemoteRequest;

use AmpProject\Exception\FailedToGetFromRemoteUrl;
use AmpProject\RemoteGetRequest;
use AmpProject\Response;
use Exception;
use LogicException;

/**
 * Fetch the response for a remote request from the local filesystem instead.
 *
 * This can be used to provide offline fallbacks.
 *
 * @package ampproject/amp-toolbox
 */
final class FilesystemRemoteGetRequest implements RemoteGetRequest
{

    /**
     * Associative array of data for mapping between arguments and filepaths pointing to the results to return.
     *
     * @var array
     */
    private $argumentMap;

    /**
     * Instantiate a FilesystemRemoteGetRequest object.
     *
     * @param array $argumentMap Associative array of data for mapping between arguments and filepaths pointing to the
     *                           results to return.
     */
    public function __construct($argumentMap)
    {
        $this->argumentMap = $argumentMap;
    }

    /**
     * Do a GET request to retrieve the contents of a remote URL.
     *
     * @param string $url URL to get.
     * @return Response Response for the executed request.
     * @throws FailedToGetFromRemoteUrl If retrieving the contents from the URL failed.
     * @throws LogicException If invalid file path and/or invalid or non-readable file.
     */
    public function get($url)
    {
        if (! array_key_exists($url, $this->argumentMap)) {
            throw new LogicException("Trying to get a remote request from the filesystem for an unknown URL: {$url}.");
        }

        if (! file_exists($this->argumentMap[$url]) || ! is_readable($this->argumentMap[$url])) {
            throw new LogicException(
                'Trying to get a remote request from the filesystem for a file that is not accessible: '
                . "{$url} => {$this->argumentMap[$url]}."
            );
        }

        try {
            return new RemoteGetRequestResponse(file_get_contents($this->argumentMap[$url]));
        } catch (Exception $exception) {
            throw FailedToGetFromRemoteUrl::withException($url, $exception);
        }
    }
}
