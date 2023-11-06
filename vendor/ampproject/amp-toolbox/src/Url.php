<?php

namespace AmpProject;

use AmpProject\Exception\FailedToParseUrl;

/**
 * Helper class to work with URLs.
 *
 * @property-read string|null $scheme
 * @property-read string|null $host
 * @property-read string|null $port
 * @property-read string|null $user
 * @property-read string|null $pass
 * @property-read string|null $path
 * @property-read string|null $query
 * @property-read string|null $fragment
 *
 * @package ampproject/amp-toolbox
 */
final class Url
{
    const SCHEME   = 'scheme';
    const HOST     = 'host';
    const PORT     = 'port';
    const USER     = 'user';
    const PASS     = 'pass';
    const PATH     = 'path';
    const QUERY    = 'query';
    const FRAGMENT = 'fragment';

    /**
     * Regular expression pattern used to collapse the current path ('.').
     *
     * @var string
     */
    const COLLAPSE_CURRENT_PATHS_REGEX_PATTERN = '#/\./#';

    /**
     * Regular expression pattern used to collapse a relative path ('..').
     *
     * @var string
     */
    const COLLAPSE_RELATIVE_PATHS_REGEX_PATTERN = '#(?<=/)(?!\.\./)[^/]+/\.\./#';

    /**
     * Error message to use when the __get() is triggered for an unknown property.
     *
     * @var string
     */
    const PROPERTY_GETTER_ERROR_MESSAGE = 'Undefined property: AmpProject\\Url::';

    /**
     * Scheme.
     *
     * @var string|null
     */
    private $scheme;

    /**
     * Host.
     *
     * @var string|null
     */
    private $host;

    /**
     * Port.
     *
     * @var string|null
     */
    private $port;

    /**
     * User.
     *
     * @var string|null
     */
    private $user;

    /**
     * Password.
     *
     * @var string|null
     */
    private $pass;

    /**
     * Query string.
     *
     * @var string|null
     */
    private $query;

    /**
     * Path.
     *
     * @var string|null
     */
    private $path;

    /**
     * Fragment.
     *
     * @var string|null
     */
    private $fragment;

    /**
     * Default URL parts to use when constructing an absolute URL out of a relative one.
     *
     * @var array{
     *    scheme: string,
     *    host: string,
     *    port: null,
     *    user: null,
     *    pass: null,
     *    path: null,
     *    query: null,
     *    fragment: null
     * }
     */
    const URL_DEFAULT_PARTS = [
        self::SCHEME   => 'https',
        self::HOST     => 'example.com',
        self::PORT     => null,
        self::USER     => null,
        self::PASS     => null,
        self::PATH     => null,
        self::QUERY    => null,
        self::FRAGMENT => null,
    ];

    /**
     * Url constructor.
     *
     * @param string|null $url     URL.
     * @param Url|null    $baseUrl Base URL.
     * @throws FailedToParseUrl Exception when the URL or Base URL is malformed.
     */
    public function __construct($url = null, Url $baseUrl = null)
    {
        $parsedUrl = [];

        if ($url !== null) {
            $parsedUrl = parse_url($url);
            if (false === $parsedUrl) {
                throw FailedToParseUrl::forUrl($url);
            }
        }

        if ($baseUrl) {
            if (isset($parsedUrl[self::PATH]) && 0 !== strpos($parsedUrl[self::PATH], '/')) {
                $root                  = rtrim($baseUrl->path, '/');
                $parsedUrl[self::PATH] = $this->unrelativizePath("{$root}/{$parsedUrl[self::PATH]}");
            }
            $parsedUrl = array_merge($baseUrl->toArray(), $parsedUrl);
        }

        if ($parsedUrl) {
            foreach ($parsedUrl as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Eliminate relative segments (../ and ./) from a path.
     *
     * @param string $path Path with relative segments. This is not a URL, so no host and no query string.
     * @return string Unrelativized path.
     */
    private function unrelativizePath($path)
    {

        // Eliminate current directory relative paths, like <foo/./bar/./baz.css> => <foo/bar/baz.css>.
        do {
            $path = preg_replace(
                self::COLLAPSE_CURRENT_PATHS_REGEX_PATTERN,
                '/',
                $path,
                -1,
                $count
            );
        } while (0 !== $count);

        // Collapse relative paths, like <foo/bar/../../baz.css> => <baz.css>.
        do {
            $path = preg_replace(
                self::COLLAPSE_RELATIVE_PATHS_REGEX_PATTERN,
                '',
                $path,
                1,
                $count
            );
        } while (0 !== $count);

        return $path;
    }

    /**
     * Check whether the URL is a valid image source URL.
     *
     * @return bool Whether the src string is a valid image source URL.
     */
    public function isValidNonDataUrl()
    {
        // Bail early on 'data:' assets.
        if ($this->scheme === 'data') {
            return false;
        }

        // @TODO: This probably needs additional logic.

        $parts = array_merge(self::URL_DEFAULT_PARTS, $this->toArray(true));

        return (bool)filter_var(self::toString($parts), FILTER_VALIDATE_URL);
    }

    /**
     * Get the URL parts as an associative array.
     *
     * @param bool $sparse Whether to only include parts with non-empty values.
     *
     * @return array Associative array with URL parts.
     */
    public function toArray($sparse = false)
    {
        $array = [
            self::SCHEME   => $this->scheme,
            self::HOST     => $this->host,
            self::PORT     => $this->port,
            self::USER     => $this->user,
            self::PASS     => $this->pass,
            self::PATH     => $this->path,
            self::QUERY    => $this->query,
            self::FRAGMENT => $this->fragment,
        ];

        return $sparse ? array_filter($array) : $array;
    }

    /**
     * Serialize to string.
     *
     * @return string URL.
     */
    public function __toString()
    {
        return self::toString($this->toArray());
    }

    /**
     * Return a provided URL parsed into parts as an assembled string.
     *
     * @param array $parts Parts of the URL to assemble.
     * @return string Assembled URL string.
     */
    public static function toString($parts)
    {
        $url = ! empty($parts[self::SCHEME]) ? "{$parts[self::SCHEME]}://" : '//';

        if (! empty($parts[self::USER])) {
            $url .= "{$parts[self::USER]}";
            if (! empty($parts[self::PASS])) {
                $url .= ":{$parts[self::PASS]}";
            }
            $url .= '@';
        }

        $url .= ! empty($parts[self::HOST]) ? "{$parts[self::HOST]}" : 'localhost';

        if (! empty($parts[self::PORT])) {
            $url .= ":{$parts[self::PORT]}";
        }

        $url .= ! empty($parts[self::PATH]) ? "{$parts[self::PATH]}" : '/';

        if (! empty($parts[self::QUERY])) {
            $url .= "?{$parts[self::QUERY]}";
        }

        if (! empty($parts[self::FRAGMENT])) {
            $url .= "#{$parts[self::FRAGMENT]}";
        }

        return $url;
    }

    /**
     * Magic getter to return the individual parts.
     *
     * @param string $name Name of the part to return.
     * @return string|null Part string or null if it was not found during parsing.
     */
    public function __get($name)
    {
        if (array_key_exists($name, self::URL_DEFAULT_PARTS)) {
            return $this->$name;
        }

        // Mimic regular PHP behavior for missing notices.
        trigger_error(self::PROPERTY_GETTER_ERROR_MESSAGE . $name, E_USER_NOTICE);
        return null;
    }
}
