<?php

namespace AmpProject\Optimizer;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Collection of error objects to pass around the transformation engine.
 *
 * @package ampproject/amp-toolbox
 */
final class ErrorCollection implements Countable, IteratorAggregate
{
    /**
     * Internal storage for the errors that were added.
     *
     * @var Error[]
     */
    private $errors = [];

    /**
     * Add an error to the error collection.
     *
     * @param Error $error Error to add.
     * @return void
     */
    public function add(Error $error) // phpcs:ignore PHPCompatibility.Classes.NewClasses.errorFound
    {
        $this->errors[] = $error;
    }

    /**
     * Check whether the error collection contains an error for the given code.
     *
     * @param string $code Code of the error.
     * @return bool Whether the error collection contains an error with the given code.
     */
    public function has($code)
    {
        foreach ($this->errors as $error) {
            if ($error->getCode() === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the iterator for iterating over the collection.
     *
     * @return ArrayIterator Iterator for the contained errors.
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->errors);
    }

    /**
     * Count how many errors are contained within the error collection.
     *
     * @return int Number of contained errors.
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->errors);
    }
}
