<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Optimizer\Exception\InvalidConfigurationKey;
use AmpProject\Optimizer\Exception\UnknownConfigurationKey;
use AmpProject\Optimizer\TransformerConfiguration;

/**
 * Configuration for the AmpRuntimeCss transformer.
 *
 * @property string  $version Version string to use. Defaults to an empty string.
 * @property boolean $canary  Whether to use the canary version or not. Defaults to false.
 *
 * @package ampproject/amp-toolbox
 */
abstract class BaseTransformerConfiguration implements TransformerConfiguration
{
    /**
     * Associative array of allowed keys and their respective default values.
     *
     * @var array
     */
    private $allowedKeys;

    /**
     * Associative array of configuration data.
     *
     * @var array
     */
    private $configuration = [];

    /**
     * Instantiate an AmpRuntimeCssConfiguration object.
     *
     * @param array $configuration Optional. Associative array of configuration data. Defaults to an empty array.
     */
    public function __construct($configuration = [])
    {
        $this->allowedKeys = $this->getAllowedKeys();
        $configuration     = array_merge($this->allowedKeys, $configuration);

        foreach ($configuration as $key => $value) {
            if (! array_key_exists($key, $this->allowedKeys)) {
                throw InvalidConfigurationKey::fromTransformerKey(static::class, $key);
            }
            $this->configuration[$key] = $this->validate($key, $value);
        }
    }

    /**
     * Get the value for a given key.
     *
     * The key is assumed to exist and will throw an exception if it can't be retrieved.
     * This means that all configuration entries should come with a default value.
     *
     * @param string $key Key of the configuration entry to retrieve.
     * @return mixed Value stored under the given configuration key.
     * @throws UnknownConfigurationKey If an unknown key was provided.
     */
    public function get($key)
    {
        if (! array_key_exists($key, $this->allowedKeys)) {
            throw UnknownConfigurationKey::fromTransformerKey(static::class, $key);
        }

        // At this point, the configuration should either have received this value or filled it with a default.
        return $this->configuration[$key];
    }

    /**
     * Magic getter to get value for a given key.
     *
     * Mostly for backward compatibility.
     *
     * @param string $name Name of the property to set.
     */
    public function __get($name)
    {
        if (! array_key_exists($name, $this->allowedKeys)) {
            // Mimic regular PHP behavior for missing notices.
            trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
            return null;
        }

        return $this->configuration[$name];
    }

    /**
     * Magic setter for configurations.
     *
     * Mostly for backward compatibility.
     *
     * @param string $name Name of the property to set.
     * @param mixed  $value Value of the property.
     */
    public function __set($name, $value)
    {
        if (! array_key_exists($name, $this->allowedKeys)) {
            // Mimic regular PHP behavior for missing notices.
            trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
            return;
        }

        $this->configuration[$name] = $value;
    }

    /**
     * Magic method to check whether a configuration exists.
     *
     * Mostly for backward compatibility.
     *
     * @param string $name Name of the property to set.
     */
    public function __isset($name)
    {
        if (! array_key_exists($name, $this->allowedKeys)) {
            // Mimic regular PHP behavior for missing notices.
            trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
            return false;
        }

        return isset($this->configuration[$name]);
    }

    /**
     * Get an array of configuration entries for this transformer configuration.
     *
     * @return array Associative array of configuration entries.
     */
    public function toArray()
    {
        $configArray = [];

        foreach (array_keys($this->allowedKeys) as $key) {
            $configArray[$key] = $this->configuration[$key];
        }

        return $configArray;
    }

    /**
     * Get the associative array of allowed keys and their respective default values.
     *
     * The array index is the key and the array value is the key's default value.
     *
     * @return array Associative array of allowed keys and their respective default values.
     */
    abstract protected function getAllowedKeys();

    /**
     * Validate an individual configuration entry.
     *
     * @param string $key   Key of the configuration entry to validate.
     * @param mixed  $value Value of the configuration entry to validate.
     * @return mixed Validated value.
     */
    abstract protected function validate($key, $value);
}
