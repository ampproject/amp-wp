<?php

namespace Amp\RemoteRequest;

use Amp\Exception\FailedToFetchFromRemoteUrl;
use Amp\RemoteRequest;
use Exception;

/**
 * Remote request transport using cURL.
 *
 * @package amp/common
 */
final class CurlRemoteRequest implements RemoteRequest
{

    /**
     * Default timeout value to use in seconds.
     *
     * @var int
     */
    const DEFAULT_TIMEOUT = 10;

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
     * Instantiate a CurlRemoteRequest object.
     *
     * @param bool $sslVerify Whether to verify SSL certificates.
     * @param int  $timeout   Timeout value to use in seconds.
     */
    public function __construct($sslVerify = true, $timeout = self::DEFAULT_TIMEOUT)
    {
        $this->sslVerify = $sslVerify;
        $this->timeout   = $timeout;
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
            $status = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

            if (!empty($status)) {
                throw FailedToFetchFromRemoteUrl::withHttpStatus($url, $status);
            }

            throw FailedToFetchFromRemoteUrl::withoutHttpStatus($url);
        }

        return $response;
    }
}
