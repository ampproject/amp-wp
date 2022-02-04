<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationValue;
use AmpProject\RuntimeVersion;

/**
 * Configuration for the AmpRuntimeCss transformer.
 *
 * @property bool    $canary  Whether to use the canary version or not. Defaults to false.
 * @property string  $styles  Runtime styles to use.
 * @property string  $version Version string to use. Defaults to an empty string.
 *
 * @package ampproject/amp-toolbox
 */
final class AmpRuntimeCssConfiguration extends BaseTransformerConfiguration
{
    /**
     * Configuration key that holds the flag for the canary version of the runtime style.
     *
     * @var string
     */
    const CANARY = RuntimeVersion::OPTION_CANARY;

    /**
     * Configuration key that holds the actual runtime CSS styles to use.
     *
     * If the styles are not provided, the latest runtime styles are fetched from cdn.ampproject.org.
     *
     * @var string
     */
    const STYLES = 'styles';

    /**
     * Configuration key that holds the version number to use.
     *
     * If the version is not provided, the latest runtime version is fetched from cdn.ampproject.org.
     *
     * @var string
     */
    const VERSION = 'version';

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
            self::CANARY  => false,
            self::STYLES  => '',
            self::VERSION => '',
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
            case self::CANARY:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::CANARY,
                        'boolean',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;

            case self::STYLES:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::STYLES,
                        'string',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                $value = trim($value);
                break;

            case self::VERSION:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::VERSION,
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
