<?php

namespace AmpProject\Optimizer\Error;

use ReflectionClass;

/**
 * Default set of properties and methods to use for errors.
 *
 * @package AmpProject\Optimizer
 */
trait ErrorProperties
{

    /**
     * Instantiate an Error object.
     *
     * @param string $message Message for the error.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Message of the error.
     *
     * @var string
     */
    protected $message;

    /**
     * Get the code of the error.
     *
     * @return string Code of the error.
     */
    public function getCode()
    {
        return (new ReflectionClass($this))->getShortName();
    }

    /**
     * Get the message of the error.
     *
     * @return string Message of the error.
     */
    public function getMessage()
    {
        return $this->message;
    }
}
