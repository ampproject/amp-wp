<?php

namespace Amp\Optimizer;

use Amp\Optimizer\Exception\InvalidConfigurationValue;
use Amp\Optimizer\Exception\UnknownConfigurationKey;
use Amp\Optimizer\Transformer\ServerSideRendering;
use Amp\Optimizer\Transformer\TransformedIdentifier;

final class Configuration
{

    const KEY_TRANSFORMERS = 'transformers';

    /**
     * Array of known configuration keys and their default values.
     *
     * @var string[]
     */
    const DEFAULTS = [
        self::KEY_TRANSFORMERS => self::DEFAULT_TRANSFORMERS,
    ];

    /**
     * Array of FQCNs of transformers to use for the default setup.
     *
     * @var string[]
     */
    const DEFAULT_TRANSFORMERS = [
        TransformedIdentifier::class,
        ServerSideRendering::class,
    ];

    /**
     * Associative array of already validated configuration settings.
     *
     * @var array
     */
    private $configuration;

    /**
     * Instantiate a Configuration object.
     *
     * @param array $configurationData Optional. Associative array of configuration data to use. This will be merged
     *                                 with the default configuration and take precedence.
     */
    public function __construct($configurationData = [])
    {
        $this->configuration = array_merge(
            self::DEFAULTS,
            $this->validateConfigurationKeys($configurationData)
        );
    }

    /**
     * Validate an array of configuration settings.
     *
     * @param array $configurationData Associative array of configuration data to validate.
     * @return array Associative array of validated configuration data.
     */
    private function validateConfigurationKeys($configurationData)
    {
        foreach ($configurationData as $key => $value) {
            $configurationData[$key] = $this->validateConfigurationKey($key, $value);
        }

        return $configurationData;
    }

    /**
     * Validate an individual configuration setting.
     *
     * @param string $key   Key of the configuration setting.
     * @param mixed  $value Value of the configuration setting.
     * @return mixed Validated value for the provided configuration setting.
     * @throws InvalidConfigurationValue If the configuration value could not be validated.
     */
    private function validateConfigurationKey($key, $value)
    {
        switch ($key) {
            case self::KEY_TRANSFORMERS:
                if (! is_array($value)) {
                    throw InvalidConfigurationValue::forInvalidValueType(self::KEY_TRANSFORMERS, 'array', gettype($value));
                }

                foreach ($value as $index => $entry) {
                    if (! is_string($entry)) {
                        throw InvalidConfigurationValue::forInvalidSubValueType(self::KEY_TRANSFORMERS, $index, 'string', gettype($entry));
                    }
                }
        }

        return $value;
    }

    /**
     * Check whether the configuration has a given setting.
     *
     * @param string $key Configuration key to look for.
     * @return bool Whether the requested configuration key was found or not.
     */
    public function has($key)
    {
        return array_key_exists($key, $this->configuration);
    }

    /**
     * Get the value for a given key from the configuration.
     *
     * @param string $key Configuration key to get the value for.
     * @return mixed Configuration value for the requested key.
     * @throws UnknownConfigurationKey If the key was not found.
     */
    public function get($key)
    {
        if (! array_key_exists($key, $this->configuration)) {
            throw UnknownConfigurationKey::fromKey($key);
        }

        return $this->configuration[$key];
    }
}
