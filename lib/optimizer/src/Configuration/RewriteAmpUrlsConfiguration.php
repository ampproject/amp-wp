<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Amp;
use AmpProject\Optimizer\Exception\InvalidConfigurationValue;

final class RewriteAmpUrlsConfiguration extends BaseTransformerConfiguration
{

    const AMP_URL_PREFIX = 'ampUrlPrefix';

    const AMP_RUNTIME_VERSION = 'ampRuntimeVersion';

    const LTS = 'lts';

    const RTV = 'rtv';

    const GEO_API_URL = 'geoApiUrl';

    /**
     * Whether to use ES modules for loading the AMP runtime and components.
     *
     * ==> EXPERIMENTAL <==
     *
     * @var string
     */
    const EXPERIMENTAL_ESM = 'experimentalEsm';

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
            self::AMP_URL_PREFIX      => Amp::CACHE_HOST,
            self::AMP_RUNTIME_VERSION => '',
            self::LTS                 => false,
            self::RTV                 => false,
            self::GEO_API_URL         => '',
            self::EXPERIMENTAL_ESM    => false,
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
            case self::AMP_URL_PREFIX:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::AMP_URL_PREFIX,
                        'string',
                        gettype($value)
                    );
                }
                $value = trim($value);
                break;

            case self::AMP_RUNTIME_VERSION:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::AMP_RUNTIME_VERSION,
                        'string',
                        gettype($value)
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
                        gettype($value)
                    );
                }
                break;

            case self::RTV:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::RTV,
                        'boolean',
                        gettype($value)
                    );
                }
                break;

            case self::GEO_API_URL:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::GEO_API_URL,
                        'string',
                        gettype($value)
                    );
                }
                $value = trim($value);
                break;

            case self::EXPERIMENTAL_ESM:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::EXPERIMENTAL_ESM,
                        'boolean',
                        gettype($value)
                    );
                }
                break;
        }

        return $value;
    }
}
