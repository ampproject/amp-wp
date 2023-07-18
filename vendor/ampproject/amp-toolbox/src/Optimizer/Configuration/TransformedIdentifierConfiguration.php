<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationValue;
use AmpProject\Validator\Spec\CssRuleset\AmpTransformed;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Configuration for the TransformedIdentifier transformer.
 *
 * @property int $version Version number to use. Defaults to 1.
 * @property int|false $enforcedCssMaxByteCount The max bytes count to enforce on the document, or false to not enforce.
 *                                              Defaults to max bytes for transformed spec.
 *
 * @package ampproject/amp-toolbox
 */
final class TransformedIdentifierConfiguration extends BaseTransformerConfiguration
{
    /**
     * Configuration key that holds the version number to use.
     *
     * @var string
     */
    const VERSION = 'version';

    /**
     * Configuration key that holds the max CSS byte count to enforce.
     *
     * @see \AmpProject\Dom\Document::isCssMaxByteCountEnforced()
     * @see \AmpProject\Dom\Document::enforceCssMaxByteCount()
     * @var string
     */
    const ENFORCED_CSS_MAX_BYTE_COUNT = 'enforcedCssMaxByteCount';

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
            self::VERSION                     => 1,
            self::ENFORCED_CSS_MAX_BYTE_COUNT => AmpTransformed::SPEC[SpecRule::MAX_BYTES],
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
            case self::VERSION:
                if (! is_int($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::VERSION,
                        'integer',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;
            case self::ENFORCED_CSS_MAX_BYTE_COUNT:
                if (! is_int($value) && $value !== false) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::ENFORCED_CSS_MAX_BYTE_COUNT,
                        'integer|false',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;
        }

        return $value;
    }
}
