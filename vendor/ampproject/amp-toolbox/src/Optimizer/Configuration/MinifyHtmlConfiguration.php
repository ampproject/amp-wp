<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationValue;

/**
 * Configuration for the MinifyHtml transformer.
 *
 * @property bool   $minify                Whether minification is enabled.
 * @property bool   $minifyAmpScript       Whether amp-script minification is enabled.
 * @property bool   $minifyJSON            Whether JSON data minification is enabled.
 * @property bool   $collapseWhitespace    Whether collapsing whitespace is enabled.
 * @property bool   $removeComments        Whether comments should be removed.
 * @property bool   $canCollapseWhitespace Whether whitespace can be collapsed.
 * @property bool   $inBody                Whether the node is in the body.
 * @property string $commentIgnorePattern  Regex pattern of comments to keep.
 *
 * @package ampproject/amp-toolbox
 */
final class MinifyHtmlConfiguration extends BaseTransformerConfiguration
{
    /**
     * Whether minification is enabled.
     *
     * @var string
     */
    const MINIFY = 'minify';

    /**
     * Whether amp-script minification is enabled.
     *
     * @var string
     */
    const MINIFY_AMP_SCRIPT = 'minifyAmpScript';

    /**
     * Whether JSON data minification is enabled.
     *
     * @var string
     */
    const MINIFY_JSON = 'minifyJSON';

    /**
     * Whether collapsing whitespace is enabled.
     *
     * @var string
     */
    const COLLAPSE_WHITESPACE = 'collapseWhitespace';

    /**
     * Whether comments should be removed.
     *
     * @var string
     */
    const REMOVE_COMMENTS = 'removeComments';

    /**
     * Regular expression pattern of comments to keep.
     *
     * @var string
     */
    const COMMENT_IGNORE_PATTERN = 'commentIgnorePattern';

    /**
     * Get the associative array of allowed keys and their respective default values.
     *
     * The array index is the key and the array value is the key's default value.
     *
     * @return array Associative array of allowed keys and their respective default values.
     */
    protected function getAllowedKeys()
    {
        return [
            self::MINIFY                  => true,
            self::MINIFY_AMP_SCRIPT       => false,
            self::MINIFY_JSON             => true,
            self::COLLAPSE_WHITESPACE     => false,
            self::REMOVE_COMMENTS         => true,
            self::COMMENT_IGNORE_PATTERN  => '',
        ];
    }

    /**
     * Validate an individual configuration entry.
     *
     * @param string $key   Key of the configuration entry to validate.
     * @param mixed  $value Value of the configuration entry to validate.
     * @return mixed Validated value.
     */
    protected function validate($key, $value)
    {
        switch ($key) {
            case self::MINIFY:
            case self::MINIFY_JSON:
            case self::MINIFY_AMP_SCRIPT:
            case self::COLLAPSE_WHITESPACE:
            case self::REMOVE_COMMENTS:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        $key,
                        'boolean',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;
            case self::COMMENT_IGNORE_PATTERN:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::COMMENT_IGNORE_PATTERN,
                        'string',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                $value = trim($value);
                break;
        }

        return $value;
    }
}
