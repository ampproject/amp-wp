<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationValue;

/**
 * Configuration for the PreloadHeroImage transformer.
 *
 * @property string  $preloadHeroImage Whether to preload hero images. Defaults to true.
 *
 * @package ampproject/optimizer
 */
final class PreloadHeroImageConfiguration extends BaseTransformerConfiguration
{

    /**
     * Configuration key that holds the switch to disable preloading of hero images.
     *
     * @var string
     */
    const PRELOAD_HERO_IMAGE = 'preloadHeroImage';

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
            self::PRELOAD_HERO_IMAGE => true,
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
            case self::PRELOAD_HERO_IMAGE:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::PRELOAD_HERO_IMAGE,
                        'boolean',
                        gettype($value)
                    );
                }
                break;
        }

        return $value;
    }
}
