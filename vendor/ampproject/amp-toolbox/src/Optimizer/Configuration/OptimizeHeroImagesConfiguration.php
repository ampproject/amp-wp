<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationValue;

/**
 * Configuration for the OptimizeHeroImages transformer.
 *
 * @property string $inlineStyleBackupAttribute Name of the attribute that is used to store inline styles that were
 *                                              moved to <style amp-custom>
 * @property int    $maxHeroImageCount          Maximum number of hero images that are accepted. Defaults to 2.
 * @property bool   $optimizeHeroImages         Whether to optimize hero images. Defaults to true.
 * @property bool   $preloadSrcset              Whether to enable preloading of images with a srcset attribute. Defaults
 *                                              to false.
 *
 * @package ampproject/amp-toolbox
 */
final class OptimizeHeroImagesConfiguration extends BaseTransformerConfiguration
{
    /**
     * Configuration key that holds the attribute that is used to store inline styles that
     * were moved to <style amp-custom>.
     *
     * An empty string signifies that no backup is available.
     *
     * @var string
     */
    const INLINE_STYLE_BACKUP_ATTRIBUTE = 'inlineStyleBackupAttribute';

    /**
     * Configuration key that defines how many hero images are accepted.
     *
     * @var string
     */
    const MAX_HERO_IMAGE_COUNT = 'maxHeroImageCount';

    /**
     * Configuration key that holds the switch to disable preloading of hero images.
     *
     * @var string
     */
    const OPTIMIZE_HERO_IMAGES = 'optimizeHeroImages';

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
            self::MAX_HERO_IMAGE_COUNT          => 2,
            self::OPTIMIZE_HERO_IMAGES          => true,
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

            case self::MAX_HERO_IMAGE_COUNT:
                if (! is_int($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::MAX_HERO_IMAGE_COUNT,
                        'integer',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;

            case self::OPTIMIZE_HERO_IMAGES:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::OPTIMIZE_HERO_IMAGES,
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
