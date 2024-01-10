<?php

namespace AmpProject\RemoteRequest;

use AmpProject\Response;

/**
 * Stub for simulating remote requests.
 *
 * @package ampproject/amp-toolbox
 */
final class RemoteGetRequestResponse implements Response
{
    /**
     * Body of the response.
     *
     * @var mixed
     */
    private $body;

    /**
     * Headers of the response.
     *
     * @var string[][]
     */
    private $headers;

    /**
     * Index mapping lowercase keys to actual keys in $this->headers.
     *
     * This is used for case-insensitive lookups.
     *
     * @var string[]
     */
    private $headersIndex;

    /**
     * Status code of the response.
     *
     * @var int
     */
    private $statusCode;

    /**
     * Instantiate a RemoteGetRequestResponse object.
     *
     * @param mixed      $body       Body of the response.
     * @param string[][] $headers    Headers of the response.
     * @param int        $statusCode Status code of the response.
     */
    public function __construct($body, $headers = [], $statusCode = 200)
    {
        $this->body       = $body;
        $this->headers    = $headers;
        $this->statusCode = $statusCode;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and each value is an array of strings
     * associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ': ' . implode(', ', $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the exact case in which headers were
     * originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each key MUST be a header name, and
     *                    each value MUST be an array of strings for that header.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header name using a case-insensitive string
     *              comparison. Returns false if no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        $this->maybeInitHeadersIndex();
        return array_key_exists(strtolower($name), $this->headersIndex);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given header. If the header does not appear in
     *                  the message, this method MUST return an empty array.
     */
    public function getHeader($name)
    {
        $this->maybeInitHeadersIndex();
        $key = strtolower($name);

        if (! array_key_exists($key, $this->headersIndex)) {
            return [];
        }

        return (array) $this->headers[$this->headersIndex[$key]];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given case-insensitive header name as a string concatenated
     * together using a comma.
     *
     * NOTE: Not all header values may be appropriately represented using comma concatenation. For such headers, use
     * getHeader() instead and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header concatenated together using a comma. If the
     *                header does not appear in the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        $key = strtolower($name);

        if (! array_key_exists($key, $this->headersIndex)) {
            return '';
        }

        return implode(',', (array)$this->headers[$this->headersIndex[$key]]);
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the body of the response.
     *
     * @return mixed Body of the response.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Initial the headersIndex for case-insensitive lookups if that hasn't been done yet.
     */
    private function maybeInitHeadersIndex()
    {
        if ($this->headersIndex !== null) {
            return;
        }

        $this->headersIndex = array_combine(array_keys($this->headers), array_keys($this->headers));
        $this->headersIndex = array_change_key_case($this->headersIndex, CASE_LOWER);
    }
}
