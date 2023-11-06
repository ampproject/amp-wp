<?php

namespace AmpProject;

/**
 * Helper class for dealing with multibyte-relevant string operations.
 *
 * @package ampproject/amp-toolbox
 */
final class Str
{
    /**
     * Whether to try to use multibyte functions.
     *
     * This can be used to forcefully disable multibyte functions even if they are available.
     *
     * @var bool
     */
    private static $useMultibyte = true;

    /**
     * Enable support for multibyte functions if they are available.
     */
    public static function enableMultibyte()
    {
        self::$useMultibyte = true;
    }

    /**
     * Disable support for multibyte functions, even if they are available.
     */
    public static function disableMultibyte()
    {
        self::$useMultibyte = false;
    }

    /**
     * Set the encoding that the string helper methods should use.
     *
     * This is simply being ignored if multi-byte support is not available.
     *
     * @param string $encoding Encoding to set the string helper methods to.
     */
    public static function setEncoding($encoding)
    {
        if (! self::$useMultibyte) {
            return;
        }

        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding($encoding);
        }

        if (function_exists('mb_regex_encoding')) {
            mb_regex_encoding($encoding);
        }
    }

    /**
     * Get the length of the text in characters.
     *
     * @param string $text Text to get the length of.
     *
     * @return int Length of the text in characters.
     */
    public static function length($text)
    {
        return (self::$useMultibyte && function_exists('mb_strlen'))
            ? mb_strlen($text)
            : strlen($text);
    }

    /**
     * Extract a substring from a string of text.
     *
     * @param string   $text   Text to extract the substring from.
     * @param int      $offset Offset into the text.
     * @param int|null $length Optional. Length of the substring to extract.
     *
     * @return string Substring extracted from the text.
     */
    public static function substring($text, $offset, $length = null)
    {
        if (self::$useMultibyte && function_exists('mb_substr')) {
            // Up until PHP 8, passing $length=null is not the same as not passing the $length argument.
            if ($length !== null) {
                return mb_substr($text, $offset, $length);
            }

            return mb_substr($text, $offset);
        }

        // Up until PHP 8, passing $length=null is not the same as not passing the $length argument.
        if ($length !== null) {
            return substr($text, $offset, $length);
        }

        return substr($text, $offset);
    }

    /**
     * Get the first position of a substring within the text.
     *
     * @param string $text      Text to look for the substring in.
     * @param string $substring Substring to look for.
     * @param int    $offset    Optional. Offset into the text. Defaults to 0.
     * @return int|false Position into the text at which the substring was found, or false if not found.
     */
    public static function position($text, $substring, $offset = 0)
    {
        // Make sure $offset is always an integer.
        $offset = $offset ? (int) $offset : 0;

        return (self::$useMultibyte && function_exists('mb_strpos'))
            ? mb_strpos($text, $substring, $offset)
            : strpos($text, $substring, $offset);
    }

    /**
     * Get the last position of a substring within the text.
     *
     * @param string $text      Text to look for the substring in.
     * @param string $substring Substring to look for.
     * @param int    $offset    Optional. Offset into the text. Defaults to 0.
     * @return int|false Position into the text at which the substring was found, or false if not found.
     */
    public static function lastPosition($text, $substring, $offset = 0)
    {
        // Make sure $offset is always an integer.
        $offset = $offset ? (int) $offset : 0;

        return (self::$useMultibyte && function_exists('mb_strrpos'))
            ? mb_strrpos($text, $substring, $offset)
            : strrpos($text, $substring, $offset);
    }

    /**
     * Convert text to lower case.
     *
     * @param string $text Text to convert to lower case.
     *
     * @return string Lower case text.
     */
    public static function toLowerCase($text)
    {
        return (self::$useMultibyte && function_exists('mb_strtolower'))
            ? mb_strtolower($text)
            : strtolower($text);
    }

    /**
     * Convert text to upper case.
     *
     * @param string $text Text to convert to upper case.
     *
     * @return string Upper case text.
     */
    public static function toUpperCase($text)
    {
        return (self::$useMultibyte && function_exists('mb_strtoupper'))
            ? mb_strtoupper($text)
            : strtoupper($text);
    }

    /**
     * Perform a regular expression search and replace.
     *
     * Note: This does not fully support named capture groups, due to a limitation in the mbstring extension.
     *
     * @param string $pattern  Regular expression pattern to target elements to replace.
     * @param string $text     Text to look for a match in.
     * @param array  $matches Optional. If $matches is provided, then it is filled with the results of search.
     * @return int|bool Whether the text matches the regular expression pattern.
     */
    public static function regexMatch($pattern, $text, &$matches = null)
    {
        if (self::$useMultibyte && function_exists('mb_ereg')) {
            list($pattern, $modifiers) = self::extractPatternAndModifiers($pattern);

            return self::position($modifiers, 'i') === false
                ? mb_ereg($pattern, $text, $matches)
                : mb_eregi($pattern, $text, $matches);
        }

        return preg_match($pattern, $text, $matches);
    }

    /**
     * Perform a regular expression search and replace.
     *
     * Note: This does not fully support named capture groups, due to a limitation in the mbstring extension.
     *
     * @param string $pattern     Regular expression pattern to target elements to replace.
     * @param string $replacement Replacement string.
     * @param string $subject     Subject to do the replacements with.
     * @return string|null Modified string, or null on error.
     */
    public static function regexReplace($pattern, $replacement, $subject)
    {
        if (self::$useMultibyte && function_exists('mb_ereg_replace')) {
            list($pattern, $modifiers) = self::extractPatternAndModifiers($pattern);

            return mb_ereg_replace($pattern, $replacement, $subject, $modifiers);
        }

        return preg_replace($pattern, $replacement, $subject);
    }


    /**
     * Perform a regular expression search and replace.
     *
     * @param string   $pattern  Regular expression pattern to target elements to replace.
     * @param callable $callback Replacement string.
     * @param string   $subject  Subject to do the replacements with.
     * @return string|null Modified string, or null on error.
     */
    public static function regexReplaceCallback($pattern, $callback, $subject)
    {
        if (self::$useMultibyte && function_exists('mb_ereg_replace_callback')) {
            list($pattern, $modifiers) = self::extractPatternAndModifiers($pattern);

            return mb_ereg_replace_callback($pattern, $callback, $subject, $modifiers);
        }

        return preg_replace_callback($pattern, $callback, $subject);
    }

    /**
     * Extract multi-byte regex pattern and modifiers from single-byte pattern.
     *
     * The mb_ereg_* functions don't use a separator, so we need to adapt the preg_* patterns.
     *
     * @param string $pattern Regular expression preg_* pattern that we need to adapt for mb_ereg_*.
     * @return array Array with the adapted pattern and the modifiers.
     */
    private static function extractPatternAndModifiers($pattern)
    {
        $separator            = self::substring($pattern, 0, 1);
        $secondSeparatorIndex = self::lastPosition($pattern, $separator, 1);
        $modifiers            = self::substring($pattern, $secondSeparatorIndex + 1);
        $pattern              = self::substring($pattern, 1, $secondSeparatorIndex - 1);

        // UTF-8 flag 'u' from preg_* means "GNU regex" for mb_ereg_* functions, so we better strip it.
        $modifiers = str_replace('u', '', $modifiers);

        return [$pattern, $modifiers];
    }
}
