<?php

namespace Amp\RemoteRequest;

use Amp\Exception\FailedToGetFromRemoteUrl;
use Amp\RemoteGetRequest;

/**
 * Remote request transport using cURL.
 *
 * @package amp/common
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
        CURLE_HTTP_NOT_FOUND,
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
     * @param string $url URL to get.
     * @return string Contents retrieved from the remote URL.
     * @throws FailedToGetFromRemoteUrl If retrieving the contents from the URL failed.
     */
    public function get($url)
    {
        $retriesLeft = $this->retries;
        do {
            $curlHandle = curl_init();

            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, $this->sslVerify ? 1 : 0);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, $this->sslVerify ? 2 : 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, $this->timeout);

            $response = curl_exec($curlHandle);
            curl_close($curlHandle);

            if ($response === false) {
                $curlErrno = curl_errno($curlHandle);

                if (! $retriesLeft || in_array($curlErrno, self::RETRYABLE_ERROR_CODES, true) === false) {
                    $status = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

                    if (! empty($status)) {
                        throw FailedToGetFromRemoteUrl::withHttpStatus($url, $status);
                    }

                    throw FailedToGetFromRemoteUrl::withoutHttpStatus($url);
                }

                continue;
            }

            return $response;
        } while ($retriesLeft--);
    }
}
