<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationValue;

/**
 * Configuration for the TransformedIdentifier transformer.
 *
 * @property int $version Version number to use. Defaults to 0.
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
     * Get the associative array of allowed keys and their respective default values.
     *
     * The array index is the key and the array value is the key's default value.
     *
     * @return array Associative array of allowed keys and their respective default values.
     */
    protected function getAllowedKeys()
    {
        return [
            self::VERSION => 1,
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
        }

        return $value;
    }
}
