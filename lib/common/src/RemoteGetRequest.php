<?php

namespace Amp;

use Amp\Exception\FailedToGetFromRemoteUrl;

/**
 * Interface for abstracting away the transport that is being used for making remote requests.
 *
 * This allows external code to replace the transport and tests to mock it.
 *
 * @package amp/common
 */
interface RemoteGetRequest
{

    /**
     * Do a GET request to retrieve the contents of a remote URL.
     *
     * @param string $url URL to get.
     * @return Response Response for the executed request.
     * @throws FailedToGetFromRemoteUrl If retrieving the contents from the URL failed.
     */
    public function get($url);
}
