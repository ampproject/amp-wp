<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationValue;

/**
 * Configuration for the PreloadHeroImage transformer.
 *
 * @property string $inlineStyleBackupAttribute Name of the attribute that is used to store inline styles that were
 *                                              moved to <style amp-custom>
 * @property bool   $preloadHeroImage           Whether to preload hero images. Defaults to true.
 * @property bool   $preloadSrcset              Whether to enable preloading of images with a srcset attribute. Defaults
 *                                              to false.
 * @deprecated since version 0.6.0
 * @see \AmpProject\Optimizer\Configuration\OptimizeHeroImagesConfiguration
 *
 * @package ampproject/amp-toolbox
 */
final class PreloadHeroImageConfiguration extends BaseTransformerConfiguration
{
    /**
     * Configuration key that holds the attribute that is used to store inline styles that
     * were moved to <style amp-custom>.
     *
     * An empty string signifies that no backup is available.
     *
     * @var string.
     */
    const INLINE_STYLE_BACKUP_ATTRIBUTE = 'inlineStyleBackupAttribute';

    /**
     * Configuration key that holds the switch to disable preloading of hero images.
     *
     * @var string
     */
    const PRELOAD_HERO_IMAGE = 'preloadHeroImage';

    /**
     * Configuration key that holds the switch to enable preloading of images with a srcset attribute.
     *
     * @var string
     */
    const PRELOAD_SRCSET = 'preloadSrcset';

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
            self::INLINE_STYLE_BACKUP_ATTRIBUTE => '',
            self::PRELOAD_HERO_IMAGE            => true,
            self::PRELOAD_SRCSET                => false,
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
            case self::INLINE_STYLE_BACKUP_ATTRIBUTE:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::INLINE_STYLE_BACKUP_ATTRIBUTE,
                        'string',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;

            case self::PRELOAD_HERO_IMAGE:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::PRELOAD_HERO_IMAGE,
                        'boolean',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;

            case self::PRELOAD_SRCSET:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::PRELOAD_SRCSET,
                        'boolean',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;
        }

        return $value;
    }
}
