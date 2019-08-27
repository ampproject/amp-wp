<?php namespace FasterImage;

use FasterImage\Exception\InvalidImageException;
use WillWashburn\Stream\Exception\StreamBufferTooSmallException;
use WillWashburn\Stream\Stream;

/**
 * FasterImage - Because sometimes you just want the size, and you want them in
 * parallel!
 *
 * Based on the PHP stream implementation by Tom Moor (http://tommoor.com)
 * which was based on the original Ruby Implementation by Steven Sykes
 * (https://github.com/sdsykes/fastimage)
 *
 * MIT Licensed
 */
class FasterImage
{
    /**
     * The default timeout.
     *
     * @var int
     */
    protected $timeout = 10;

    /**
     * The default buffer size.
     *
     * @var int
     */
    protected $bufferSize = 256;

    /**
     * The default for whether to verify SSL peer.
     *
     * @var bool
     */
    protected $sslVerifyPeer = false;

    /**
     * The default for whether to verify SSL host.
     *
     * @var bool
     */
    protected $sslVerifyHost = false;

    /**
     * If the content length should be included in the result set.
     *
     * @var bool
     */
    protected $includeContentLength = false;

    /**
     * The default user agent to set for requests.
     *
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36';

    /**
     * Get the size of each of the urls in a list
     *
     * @param string[] $urls URLs to fetch.
     *
     * @return array Results.
     * @throws \Exception When the cURL write callback fails to amend the $results.
     */
    public function batch(array $urls)
    {
        // @codeCoverageIgnoreStart
        /**
         * It turns out that even when cURL is installed, the `curl_multi_init()
         * function may be disabled on some hosts who are seeking to guard against
         * DDoS attacks.
         *
         * @see https://github.com/ampproject/amp-wp/pull/2183#issuecomment-491506514
         *
         * If it is disabled, we will batch these synchronously (with a significant
         * performance hit).
         */
        $has_curl_multi = (
            function_exists( 'curl_multi_init' )
            &&
            function_exists( 'curl_multi_exec' )
            &&
            function_exists( 'curl_multi_add_handle' )
            &&
            function_exists( 'curl_multi_select' )
            &&
            defined( 'CURLM_OK' )
            &&
            defined( 'CURLM_CALL_MULTI_PERFORM' )
        );
        if ( ! $has_curl_multi ) {
            return $this->batchSynchronously($urls);
        }
        // @codeCoverageIgnoreEnd

        $multi   = curl_multi_init();
        $results = array();

        // Create the curl handles and add them to the multi_request
        foreach ( array_values($urls) as $count => $uri ) {

            $results[$uri] = [];

            $$count = $this->handle($uri, $results[$uri]);

            $code = curl_multi_add_handle($multi, $$count);

            if ( $code != CURLM_OK ) {
                throw new \Exception("Curl handle for $uri could not be added");
            }
        }

        // Perform the requests
        do {
            while ( ($mrc = curl_multi_exec($multi, $active)) == CURLM_CALL_MULTI_PERFORM ) ;
            if ( $mrc != CURLM_OK && $mrc != CURLM_CALL_MULTI_PERFORM ) {
                throw new \Exception("Curl error code: $mrc");
            }

            if ( $active && curl_multi_select($multi) === -1 ) {
                // Perform a usleep if a select returns -1.
                // See: https://bugs.php.net/bug.php?id=61141
                usleep(250);
            }
        } while ( $active );

        // Figure out why individual requests may have failed
        foreach ( array_values($urls) as $count => $uri ) {
            $error = curl_error($$count);

            if ( $error ) {
                $results[$uri]['failure_reason'] = $error;
            }
        }

        return $results;
    }

    /**
     * Get the size of each of the urls in a list, using synchronous method
     *
     * @param string[] $urls URLs to fetch.
     *
     * @return array Results.
     * @throws \Exception When the cURL write callback fails to amend the $results.
     * @codeCoverageIgnore
     */
    protected function batchSynchronously(array $urls) {
        $results = [];
        foreach ( array_values($urls) as $count => $uri ) {
            $results[$uri] = [];

            $ch = $this->handle($uri, $results[$uri]);

            curl_exec($ch);

            // We can't check return value because the buffer size is too small and curl_error() will always be "Failed writing body".
            if ( empty($results[$uri]) ) {
                throw new \Exception("Curl handle for $uri could not be executed");
            }

            curl_close($ch);
        }
        return $results;
    }

    /**
     * @param int $seconds
     */
    public function setTimeout($seconds)
    {
        $this->timeout = (int) $seconds;
    }

    /**
     * @param int $bufferSize
     */
    public function setBufferSize($bufferSize)
    {
        $this->bufferSize = (int) $bufferSize;
    }

    /**
     * @param bool $sslVerifyPeer
     */
    public function setSslVerifyPeer($sslVerifyPeer)
    {
        $this->sslVerifyPeer = (bool) $sslVerifyPeer;
    }

    /**
     * @param bool $sslVerifyHost
     */
    public function setSslVerifyHost($sslVerifyHost)
    {
        $this->sslVerifyHost = (bool) $sslVerifyHost;
    }

    /**
     * @param bool $bool
     */
    public function setIncludeContentLength($bool)
    {
        $this->includeContentLength = (bool) $bool;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * Create the handle for the curl request
     *
     * @param $url
     * @param $result
     *
     * @return resource
     */
    protected function handle($url, & $result)
    {
        $stream           = new Stream();
        $parser           = new ImageParser($stream);
        $result['rounds'] = 0;
        $result['bytes']  = 0;
        $result['size']   = 'failed';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, $this->bufferSize);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer ? 1 : 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->sslVerifyHost ? 2 : 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        #  Some web servers require the useragent to be not a bot. So we are liars.
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: image/webp,image/*,*/*;q=0.8",
            "Cache-Control: max-age=0",
            "Connection: keep-alive",
            "Keep-Alive: 300",
            "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
            "Accept-Language: en-us,en;q=0.5",
            "Pragma: ", // browsers keep this blank.
        ]);
        curl_setopt($ch, CURLOPT_ENCODING, "");


        /*
         * We parse the headers to find the content-length. This is added to the
         * result array and can be useful to determine the overall image filesize.
         */
        if ($this->includeContentLength) {

            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$result) {

                $len    = strlen($header);
                $header = explode(':', $header, 2);

                if ( strtolower($header[0]) === 'content-length' ) {
                    $result['content-length'] = trim($header[1]);
                }

                return $len;
            });
        }

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $str) use (& $result, & $parser, & $stream, $url) {

            $result['rounds']++;
            $result['bytes'] += strlen($str);

            $stream->write($str);

            try {
                // store the type in the result array by looking at the bits
                $result['type'] = $parser->parseType();

                /*
                 * We try here to parse the buffer of characters we already have
                 * for the size.
                 */
                $result['size'] = $parser->parseSize() ?: 'failed';
            }
            catch (StreamBufferTooSmallException $e) {
                /*
                 * If this exception is thrown, we don't have enough of the stream buffered
                 * so in order to tell curl to keep streaming we need to return the number
                 * of bytes we have already handled
                 *
                 * We set the 'size' to 'failed' in the case that we've done
                 * the entire image and we couldn't figure it out. Otherwise
                 * it'll get overwritten with the next round.
                 */
                $result['size'] = 'failed';

                return strlen($str);
            }
            catch (InvalidImageException $e) {

                /*
                 * This means we've determined that we're lost and don't know
                 * how to parse this image.
                 *
                 * We set the size to invalid and move on
                 */
                $result['size'] = 'invalid';

                return -1;
            }


            /*
             * We return -1 to abort the transfer when we have enough buffered
             * to find the size
             */
            //
            // hey curl! this is an error. But really we just are stopping cause
            // we already have what we want
            return -1;
        });

        return $ch;
    }
}
