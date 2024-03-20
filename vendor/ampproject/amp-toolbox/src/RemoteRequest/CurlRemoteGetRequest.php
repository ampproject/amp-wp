<?php

namespace AmpProject\RemoteRequest;

use AmpProject\Exception\FailedRemoteRequest;
use AmpProject\Exception\FailedToGetFromRemoteUrl;
use AmpProject\RemoteGetRequest;
use AmpProject\Response;
use AmpProject\Exception\FailedToParseUrl;

/**
 * Remote request transport using cURL.
 *
 * @package ampproject/amp-toolbox
 */
final class CurlRemoteGetRequest implements RemoteGetRequest
{
    /**
     * Default timeout value to use in seconds.
     *
     * @var int
     */
    const DEFAULT_TIMEOUT = 10;

    /**
     * Default number of retry attempts to do.
     *
     * @var int
     */
    const DEFAULT_RETRIES = 2;

    /**
     * List of cURL error codes that are worth retrying for.
     *
     * @var int[]
     */
    const RETRYABLE_ERROR_CODES = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_CONNECT,
        CURLE_READ_ERROR,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_HTTP_POST_ERROR,
        CURLE_SSL_CONNECT_ERROR,
    ];

    /**
     * Whether to verify SSL certificates or not.
     *
     * @var boolean
     */
    private $sslVerify;

    /**
     * Timeout value to use in seconds.
     *
     * @var int
     */
    private $timeout;

    /**
     * Number of retry attempts to do for an error that is worth retrying.
     *
     * @var int
     */
    private $retries;

    /**
     * Instantiate a CurlRemoteGetRequest object.
     *
     * @param bool $sslVerify Optional. Whether to verify SSL certificates. Defaults to true.
     * @param int  $timeout   Optional. Timeout value to use in seconds. Defaults to 10.
     * @param int  $retries   Optional. Number of retry attempts to do if an error code was thrown that is worth
     *                        retrying. Defaults to 2.
     */
    public function __construct($sslVerify = true, $timeout = self::DEFAULT_TIMEOUT, $retries = self::DEFAULT_RETRIES)
    {
        if (! is_int($timeout) || $timeout < 0) {
            $timeout = self::DEFAULT_TIMEOUT;
        }

        if (! is_int($retries) || $retries < 0) {
            $retries = self::DEFAULT_RETRIES;
        }

        $this->sslVerify = $sslVerify;
        $this->timeout   = $timeout;
        $this->retries   = $retries;
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
        $retriesLeft = $this->retries;

        if (! is_string($url) || empty($url)) {
            throw FailedToGetFromRemoteUrl::withException($url, FailedToParseUrl::forUrl($url));
        }

        do {
            $curlHandle = curl_init();

            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_HEADER, false);
            curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, $this->sslVerify);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, $this->sslVerify ? 2 : 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, $this->timeout);

            curl_setopt(
                $curlHandle,
                CURLOPT_HEADERFUNCTION,
                static function ($curl, $header) use (&$headers) {
                    $length = strlen($header);
                    $header = array_map('trim', explode(':', $header, 2));

                    // Only store valid headers, discard invalid ones that choke on the explode.
                    if (count($header) === 2) {
                        $headers[$header[0]][] = $header[1];
                    }

                    return $length;
                }
            );

            $body   = curl_exec($curlHandle);
            $status = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

            $curlErrno = curl_errno($curlHandle);
            curl_close($curlHandle);

            if ($body === false || $status < 200 || $status >= 300) {
                if (! $retriesLeft || in_array($curlErrno, self::RETRYABLE_ERROR_CODES, true) === false) {
                    if (! empty($status) && is_numeric($status)) {
                        throw FailedToGetFromRemoteUrl::withHttpStatus($url, (int) $status);
                    }

                    throw FailedToGetFromRemoteUrl::withoutHttpStatus($url);
                }

                continue;
            }

            return new RemoteGetRequestResponse($body, $headers, (int) $status);
        } while ($retriesLeft--);

        // This should never be triggered, but we want to ensure we always have a typed return value,
        // to make PHPStan happy.
        return new RemoteGetRequestResponse('', [], 500);
    }
}
