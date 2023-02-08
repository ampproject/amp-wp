<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationValue;

/**
 * Configuration for the OptimizeAmpBind transformer.
 *
 * @property bool $enabled Whether the amp-bind optimizer is enabled.
 *
 * @package ampproject/amp-toolbox
 */
final class OptimizeAmpBindConfiguration extends BaseTransformerConfiguration
{
    /**
     * Whether the amp-bind optimizer is enabled.
     *
     * @var string
     */
    const ENABLED = 'enabled';

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
            self::ENABLED => true,
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
            case self::ENABLED:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::ENABLED,
                        'boolean',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;
        }

        return $value;
    }
}
