<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

/**
 * Convenience trait to help implement the IterableSection interface.
 *
 * @package ampproject/amp-toolbox
 */
trait Iteration
{
    /**
     * Array to use for iteration.
     *
     * @var string[]
     */
    private $iterationArray;

    /**
     * Return the current iterable object.
     *
     * @return object Tag object.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $this->initIterationArray();

        $key = current($this->iterationArray);

        return $this->findByKey($key);
    }

    /**
     * Move forward to next iterable object.
     *
     * @return void Any returned value is ignored.
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->initIterationArray();

        next($this->iterationArray);
    }

    /**
     * Return the ID of the current iterable object.
     *
     * @return string|null ID of the current iterable object, or null if out of bounds.
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        $this->initIterationArray();

        return key($this->iterationArray);
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        $this->initIterationArray();

        $key = $this->key();

        return $key !== null && $key !== false;
    }

    /**
     * Rewind the Iterator to the first iterable object.
     *
     * @return void Any returned value is ignored.
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->initIterationArray();

        reset($this->iterationArray);
    }

    /**
     * Initialize the iteration array.
     */
    private function initIterationArray()
    {
        if ($this->iterationArray === null) {
            $this->iterationArray = $this->getAvailableKeys();
        }
    }

    /**
     * Count elements of an iterable section.
     *
     * @return int The custom count as an integer.
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->getAvailableKeys());
    }

    /**
     * Get the list of available keys.
     *
     * @return array<string> Array of available keys.
     */
    abstract public function getAvailableKeys();

    /**
     * Find the instantiated object for the current key.
     *
     * This should use its own caching mechanism as needed.
     *
     * @param string $key Key to retrieve the instantiated object for.
     * @return object Instantiated object for the current key.
     */
    abstract public function findByKey($key);
}
