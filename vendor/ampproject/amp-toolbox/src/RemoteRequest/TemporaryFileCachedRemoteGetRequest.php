<?php

namespace AmpProject\RemoteRequest;

use AmpProject\Exception\FailedToGetCachedResponse;
use AmpProject\RemoteGetRequest;
use AmpProject\Response;
use DateTimeImmutable;
use Exception;

/**
 * Temporarily cache remote response.
 *
 * @package ampproject/amp-toolbox
 */
final class TemporaryFileCachedRemoteGetRequest implements RemoteGetRequest
{
    /**
     * Prefix to use to identify cached file.
     *
     * @var string
     */
    const CACHED_FILE_PREFIX = 'amp-remote-request-';

    /**
     * Cache control header directive name.
     *
     * @var string
     */
    const CACHE_CONTROL = 'Cache-Control';

    /**
     * Default cache expiration time in seconds.
     *
     * The number represents one month time in seconds. It will be used by default for successful requests when
     * the 'cache-control: max-age' was not provided.
     *
     * @var int
     */
    const EXPIRY_TIME = 2592000;

    /**
     * The decorated RemoteGetRequest instance.
     *
     * @var RemoteGetRequest
     */
    private $remoteGetRequest;

    /**
     * Absolute path to the directory where temporary files are saved.
     *
     * @var string
     */
    private $directory;

    /**
     * Instantiate a TemporaryFileCachedRemoteGetRequest object.
     *
     * @param RemoteGetRequest $remoteGetRequest The decorated RemoteGetRequest object.
     * @param string           $directory        Optional. Absolute path to the directory where temporary files are
     *                                           saved.
     */
    public function __construct(RemoteGetRequest $remoteGetRequest, $directory = '')
    {
        $this->remoteGetRequest = $remoteGetRequest;
        $this->directory        = $directory ? $directory : sys_get_temp_dir();
    }

    /**
     * Get the cached request from a temporary file.
     *
     * @param string $url     URL to get.
     * @param array  $headers Optional. Associative array of headers to send with the request. Defaults to empty array.
     * @return Response Response for the executed request.
     * @throws FailedToGetCachedResponse If retrieving the contents from the cache failed.
     */
    public function get($url, $headers = [])
    {
        $file = $this->getTemporaryFilePath($url);

        if (! file_exists($file)) {
            return $this->getRemoteResponse($url, $headers);
        }

        $cachedResponse = file_get_contents($file);

        // phpcs:disable PHPCompatibility.FunctionUse.NewFunctionParameters.unserialize_optionsFound
        if ($cachedResponse !== false) {
            if (PHP_MAJOR_VERSION >= 7) {
                $cachedResponse = unserialize($cachedResponse, [RemoteGetRequestResponse::class]);
            } else {
                // PHP 5.6 does not provide the second $options argument yet.
                $cachedResponse = unserialize($cachedResponse);
            }
        }
        // phpcs:enable PHPCompatibility.FunctionUse.NewFunctionParameters.unserialize_optionsFound

        if (! $cachedResponse instanceof RemoteGetRequestResponse || $this->isExpired($file, $cachedResponse)) {
            return $this->getRemoteResponse($url, $headers);
        }

        return $cachedResponse;
    }

    /**
     * Get the absolute path of the temporary file.
     *
     * @param string $url The request url.
     * @return string The absolute path to the temporary file.
     */
    private function getTemporaryFilePath($url)
    {
        $filename = self::CACHED_FILE_PREFIX . md5($url);
        return "{$this->directory}/{$filename}";
    }

    /**
     * Get the remote response using the decorated RemoteGetRequest object.
     *
     * @param string $url     URL to get.
     * @param array  $headers Associative array of headers to send with the request.
     * @return Response Response for the executed request.
     * @throws FailedToGetCachedResponse If the remote GET request could not be executed.
     */
    private function getRemoteResponse($url, $headers)
    {
        try {
            $response = $this->remoteGetRequest->get($url, $headers);
            $this->cacheResponse($url, $response);
            return $response;
        } catch (Exception $error) {
            throw FailedToGetCachedResponse::withUrl($url);
        }
    }

    /**
     * Save the response in a temporary file.
     *
     * @param string   $url      The request url.
     * @param Response $response The response that needs to be cached.
     */
    private function cacheResponse($url, Response $response)
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            return;
        }

        file_put_contents($this->getTemporaryFilePath($url), serialize($response));
    }

    /**
     * Check whether the request is expired.
     *
     * @param string                   $file     The absolute path of the file that contains the response data.
     * @param RemoteGetRequestResponse $response The response that needs to be checked.
     * @return bool
     */
    private function isExpired($file, RemoteGetRequestResponse $response)
    {
        $expiry = $this->getExpiryTime($file, $response);

        return microtime(true) > $expiry->getTimestamp();
    }

    /**
     * Calculate the expiry time.
     *
     * @param string                   $file     The absolute path of the file that contains the response data.
     * @param RemoteGetRequestResponse $response The response data that needs to be calculated.
     *
     * @return DateTimeImmutable Cache expiry time object.
     */
    private function getExpiryTime($file, RemoteGetRequestResponse $response)
    {
        $expiry           = self::EXPIRY_TIME;
        $fileModifiedTime = (new DateTimeImmutable())->setTimestamp(filemtime($file));

        if ($response->hasHeader(self::CACHE_CONTROL)) {
            $maxAge = $this->getMaxAge($response->getHeader(self::CACHE_CONTROL));
            $expiry = ($maxAge >= 0) ? $maxAge : $expiry;
        }

        return $fileModifiedTime->modify("{$expiry} sec");
    }

    /**
     * Extract the max-age from response header.
     *
     * @param array $cacheControlStrings Cache-Control header value.
     * @return int The max-age value of the Cache-Control header.
     */
    private function getMaxAge($cacheControlStrings)
    {
        $maxAge = -1;

        foreach ((array) $cacheControlStrings as $cacheControlString) {
            $cacheControlParts = array_map('trim', explode(',', $cacheControlString));

            foreach ($cacheControlParts as $cacheControlPart) {
                $cacheControlSettingParts = array_map('trim', explode('=', $cacheControlPart));

                if (count($cacheControlSettingParts) !== 2) {
                    continue;
                }

                if ($cacheControlSettingParts[0] === 'max-age') {
                    $maxAge = abs((int)$cacheControlSettingParts[1]);
                }
            }
        }

        return $maxAge;
    }
}
