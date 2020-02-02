<?php

namespace Amp;

use Amp\Exception\FailedToFetchFromRemoteUrl;

/**
 * Interface for abstracting away the transport that is being used for making remote requests.
 *
 * This allows external code to replace the transport and tests to mock it.
 *
 * @package amp/common
 */
interface RemoteRequest
{

    /**
     * Fetch the contents of a remote request.
     *
     * @param string $url URL to fetch.
     * @return string Contents retrieved from the remote URL.
     * @throws FailedToFetchFromRemoteUrl If fetching the contents from the URL failed.
     */
    public function fetch($url);
}
