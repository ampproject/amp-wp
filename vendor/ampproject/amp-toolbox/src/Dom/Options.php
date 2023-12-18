<?php

namespace AmpProject\Dom;

use ArrayAccess;

/**
 * Configuration options for DOM document.
 *
 * @package ampproject/amp-toolbox
 */
final class Options implements ArrayAccess
{
    /**
     * Associative array of options to configure the behavior of the DOM document abstraction.
     *
     * @var array
     */
    private $options;

    /**
     * Creates a new AmpProject\Dom\Options object
     *
     * @param array $options Associative array of configuration options.
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Sets a value at a specified offset.
     *
     * @param string $option The option name.
     * @param mixed  $value  Option value.
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Determines whether an option exists.
     *
     * @param string $option Option name.
     * @return bool True if the option exists, false otherwise.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($option)
    {
        return isset($this->options[$option]);
    }

    /**
     * Unsets a specified option.
     *
     * @param string $option Option name.
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($option)
    {
        unset($this->options[$option]);
    }

    /**
     * Retrieves a value at a specified option.
     *
     * @param string $option Option name.
     * @return mixed If set, the value of the option, null otherwise.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($option)
    {
        return isset($this->options[$option]) ? $this->options[$option] : null;
    }

    /**
     * Merge new options with the existing ones.
     *
     * @param array $options Associative array of new options.
     * @return Options Cloned version of the Options object.
     */
    public function merge($options)
    {
        $clonedOptions = clone $this;
        $clonedOptions->options = array_merge($this->options, $options);

        return $clonedOptions;
    }

    /**
     * Get the options in associative array.
     * @return array Associative array of options.
     */
    public function toArray()
    {
        return $this->options;
    }
}
