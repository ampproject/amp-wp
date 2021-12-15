<?php

namespace AmpProject\Exception;

use AmpProject\Str;
use InvalidArgumentException;

/**
 * Exception thrown when an HTML document could not be parsed.
 *
 * @package ampproject/amp-toolbox
 */
final class FailedToParseHtml extends InvalidArgumentException implements AmpException
{
    /**
     * Instantiate a FailedToParseHtml exception for a HTML that could not be parsed.
     *
     * @param string $html HTML that failed to be parsed.
     * @return self
     */
    public static function forHtml($html)
    {
        if (Str::length($html) > 80) {
            $html = Str::substring($html, 0, 77) . '...';
        }

        $message = "Failed to parse the provided HTML document ({$html}).";

        return new self($message);
    }
}
