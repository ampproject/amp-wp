<?php

namespace Amp\Optimizer\Configuration;

use Amp\Optimizer\Exception\InvalidConfigurationValue;
use Amp\Optimizer\Transformer\TransformedIdentifier;

/**
 * Configuration for the TransformedIdentifier transformer.
 *
 * @property string $version Version string to use. Defaults to an empty string.
 *
 * @package amp/optimizer
 */
final class TransformedIdentifierConfiguration extends BaseTransformerConfiguration
{

    /**
     * Configuration key that holds the version number to use.
     *
     * @var int
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
                    throw InvalidConfigurationValue::forInvalidSubValueType(TransformedIdentifier::class, self::VERSION, 'integer', gettype($value));
                }
                break;
        }

        return $value;
    }
}
