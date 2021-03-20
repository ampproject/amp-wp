<?php

namespace AmpProject;

/**
 * Helper class to work with URLs.
 *
 * @package ampproject/amp-toolbox
 */
final class Url
{

    /**
     * Default URL parts to use when constructing an absolute URL out of a relative one.
     *
     * @var string[]
     */
    const URL_DEFAULT_PARTS = [
        'scheme'   => 'https',
        'host'     => 'example.com',
        'port'     => '',
        'user'     => '',
        'pass'     => '',
        'path'     => '',
        'query'    => '',
        'fragment' => '',
    ];

    /**
     * Check whether a given src string is a valid image source URL.
     *
     * @param string $src Src string to validate.
     * @return bool Whether the src string is a valid image source URL.
     */
    public static function isValidNonDataUrl($src)
    {
        list($scheme, $host, $port, $user, $pass, $path, $query, $fragment) = array_values(
            array_merge(
                self::URL_DEFAULT_PARTS,
                (array)parse_url($src)
            )
        );

        if ($scheme === 'data') {
            return false;
        }

        $userpass = $user;

        if (! empty($pass)) {
            $userpass .= ":{$pass}";
        }

        if (! empty($userpass)) {
            $userpass .= '@';
        }

        $url = sprintf(
            '%s://%s%s%s/%s%s%s',
            $scheme,
            $userpass,
            $host,
            empty($port) ? '' : ":{$port}",
            ltrim($path, '/'),
            empty($query) ? '' : "?{$query}",
            empty($fragment) ? '' : "#{$fragment}"
        );

        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }
}
