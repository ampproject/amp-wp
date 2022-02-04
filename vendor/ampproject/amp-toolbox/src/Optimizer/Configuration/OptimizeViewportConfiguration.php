<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationValue;

/**
 * Configuration for the OptimizeViewport transformer.
 *
 * @property bool $removeInitialScaleViewportProperty Whether we should remove the initial-scale=1 in order to avoid a
 *                                                    tap delay that hurts FID.
 *
 * @package ampproject/amp-toolbox
 */
final class OptimizeViewportConfiguration extends BaseTransformerConfiguration
{
    /**
     * Whether we should remove the initial-scale=1 in order to avoid a tap delay that hurts FID.
     *
     * @var string
     */
    const REMOVE_INITIAL_SCALE_VIEWPORT_PROPERTY = 'removeInitialScaleViewportProperty';

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
            self::REMOVE_INITIAL_SCALE_VIEWPORT_PROPERTY => true,
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
            case self::REMOVE_INITIAL_SCALE_VIEWPORT_PROPERTY:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        $key,
                        'boolean',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;
        }

        return $value;
    }
}
