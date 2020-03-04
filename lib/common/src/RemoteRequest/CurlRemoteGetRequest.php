<?php

namespace Amp\RemoteRequest;

use Amp\Exception\FailedToGetFromRemoteUrl;
use Amp\RemoteGetRequest;
use Exception;

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
     * Instantiate a CurlRemoteGetRequest object.
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
     * Do a GET request to retrieve the contents of a remote URL.
     *
     * @param string $url URL to get.
     * @return string Contents retrieved from the remote URL.
     * @throws FailedToGetFromRemoteUrl If retrieving the contents from the URL failed.
     */
    public function get($url)
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
                throw FailedToGetFromRemoteUrl::withHttpStatus($url, $status);
            }

            throw FailedToGetFromRemoteUrl::withoutHttpStatus($url);
        }

        return $response;
    }
}
