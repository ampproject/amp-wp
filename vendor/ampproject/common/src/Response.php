<?php

namespace AmpProject;

/**
 * Response that was returned from a RemoteRequest execution.
 *
 * The method signatures are mostly a subset of PSR-7:
 * @see https://www.php-fig.org/psr/psr-7/
 *
 * @todo Consider using PSR-7 directly (both interfaces and a library that implements them).
 *
 * @package ampproject/common
 */
interface Response
{

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
    public function getHeaders();

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header name using a case-insensitive string
     *                     comparison. Returns false if no matching header name is found in the message.
     */
    public function hasHeader($name);

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given header. If the header does not appear in
     *                     the message, this method MUST return an empty array.
     */
    public function getHeader($name);

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
    public function getHeaderLine($name);

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode();

    /**
     * Get the body of the response.
     *
     * @return mixed Body of the response.
     */
    public function getBody();
}
