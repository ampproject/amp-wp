<?php

namespace AmpProject\RemoteRequest;

use AmpProject\Exception\FailedRemoteRequest;
use AmpProject\RemoteGetRequest;
use AmpProject\Response;
use Exception;

/**
 * Fallback pipeline implementation to go through a series of fallback requests until a request succeeds.
 *
 * The request will be tried with the first instance provided, and follow the instance series from one to the next until
 * a successful response was returned.
 *
 * A successful response is a response that doesn't return boolean false and doesn't throw an exception.
 *
 * @package ampproject/amp-toolbox
 */
final class FallbackRemoteGetRequest implements RemoteGetRequest
{
    /**
     * Array of RemoteGetRequest instances to churn through.
     *
     * @var RemoteGetRequest[]
     */
    private $pipeline;

    /**
     * Instantiate a FallbackRemoteGetRequest object.
     *
     * @param RemoteGetRequest ...$pipeline Variadic array of RemoteGetRequest instances to use as consecutive
     *                                      fallbacks.
     */
    public function __construct(RemoteGetRequest ...$pipeline)
    {
        array_walk($pipeline, [$this, 'addRemoteGetRequestInstance']);
    }

    /**
     * Add a single RemoteGetRequest instance to the pipeline.
     *
     * This adds strong typing to the variadic $pipeline argument in the constructor.
     *
     * @param RemoteGetRequest $remoteGetRequest RemoteGetRequest instance to the pipeline.
     */
    private function addRemoteGetRequestInstance(RemoteGetRequest $remoteGetRequest)
    {
        $this->pipeline[] = $remoteGetRequest;
    }

    /**
     * Do a GET request to retrieve the contents of a remote URL.
     *
     * @param string $url     URL to get.
     * @param array  $headers Optional. Associative array of headers to send with the request. Defaults to empty array.
     * @return Response Response for the executed request.
     * @throws FailedRemoteRequest If retrieving the contents from the URL failed.
     */
    public function get($url, $headers = [])
    {
        foreach ($this->pipeline as $remoteGetRequest) {
            try {
                $response = $remoteGetRequest->get($url, $headers);

                if (! $response instanceof RemoteGetRequestResponse) {
                    continue;
                }

                $statusCode = $response->getStatusCode();

                if (200 <= $statusCode && $statusCode < 300) {
                    return $response;
                }
            } catch (Exception $exception) {
                // Don't let exceptions bubble up, just continue with the next instance in the pipeline.
            }
        }

        // @todo Not sure what status code to use here. "503 Service Unavailable" is a temporary server-side error.
        return new RemoteGetRequestResponse('', [], 503);
    }
}
