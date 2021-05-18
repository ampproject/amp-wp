<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Amp;
use AmpProject\Optimizer\Exception\InvalidConfigurationValue;

/**
 * Configuration for the RewriteAmpUrls transformer.
 *
 * @property string $ampRuntimeVersion Specifies a specific version of the AMP runtime.
 * @property string $ampUrlPrefix      Specifies an URL prefix for AMP runtime URLs.
 * @property bool   $esmModulesEnabled Whether to use ES modules for loading the AMP runtime and components.
 * @property string $geoApiUrl         Specifies amp-geo API URL to use as a fallback.
 * @property bool   $lts               Use long-term stable URLs.
 * @property bool   $rtv               Append the runtime version to the rewritten URLs.
 *
 * @package ampproject/amp-toolbox
 */
final class RewriteAmpUrlsConfiguration extends BaseTransformerConfiguration
{

    /**
     * Specifies a specific version of the AMP runtime.
     *
     * For example: `ampRuntimeVersion: "001515617716922"` will result in AMP runtime URLs being re-written from
     * `https://cdn.ampproject.org/v0.js` to `https://cdn.ampproject.org/rtv/001515617716922/v0.js`.
     *
     * @var string
     */
    const AMP_RUNTIME_VERSION = 'ampRuntimeVersion';

    /**
     * Specifies an URL prefix for AMP runtime URLs.
     *
     * For example: `ampUrlPrefix: "/amp"` will result in AMP runtime URLs being re-written from
     * `https://cdn.ampproject.org/v0.js` to `/amp/v0.js`. This option is experimental and not recommended.
     *
     * @var string
     */
    const AMP_URL_PREFIX = 'ampUrlPrefix';

    /**
     * Whether to use ES modules for loading the AMP runtime and components.
     *
     * @var string
     */
    const ESM_MODULES_ENABLED = 'esmModulesEnabled';

    /**
     * Specifies amp-geo API URL to use as a fallback when `amp-geo-0.1.js` is served unpatched.
     *
     * This is used when `{{AMP_ISO_COUNTRY_HOTPATCH}}` is not replaced dynamically.
     *
     * @var string
     */
    const GEO_API_URL = 'geoApiUrl';

    /**
     * Use long-term stable URLs.
     *
     * This option is not compatible with `rtv`, `ampRuntimeVersion` or `ampUrlPrefix`; an error will be thrown if these
     * options are included together.
     *
     * Similarly, the `geoApiUrl` option is ineffective with the `lts` flag, but will simply be ignored rather than
     * throwing an error.
     *
     * @var string
     */
    const LTS = 'lts';

    /**
     * Append the runtime version to the rewritten URLs.
     *
     * This option is not compatible with `lts`.
     *
     * @var string
     */
    const RTV = 'rtv';

    /**
     * Get the associative array of allowed keys and their respective default
     * values.
     *
     * The array index is the key and the array value is the key's default
     * value.
     *
     * @return array Associative array of allowed keys and their respective
     *               default values.
     */
    protected function getAllowedKeys()
    {
        return [
            self::AMP_RUNTIME_VERSION => '',
            self::AMP_URL_PREFIX      => Amp::CACHE_HOST,
            self::ESM_MODULES_ENABLED => true,
            self::GEO_API_URL         => '',
            self::LTS                 => false,
            self::RTV                 => false,
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
            case self::AMP_RUNTIME_VERSION:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::AMP_RUNTIME_VERSION,
                        'string',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                $value = trim($value);
                break;

            case self::AMP_URL_PREFIX:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::AMP_URL_PREFIX,
                        'string',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                $value = trim($value);
                break;

            case self::ESM_MODULES_ENABLED:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::ESM_MODULES_ENABLED,
                        'boolean',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;

            case self::GEO_API_URL:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::GEO_API_URL,
                        'string',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                $value = trim($value);
                break;

            case self::LTS:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::LTS,
                        'boolean',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;

            case self::RTV:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::RTV,
                        'boolean',
                        is_object($value) ? get_class($value) : gettype($value)
                    );
                }
                break;
        }

        return $value;
    }
}
