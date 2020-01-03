<?php

namespace Amp\Optimizer\Error;

trait ErrorProperties
{

    /**
     * Instantiate an Error object.
     *
     * @param string $message Message for the error.
     */
    public function __construct($message)
    {
        $this->code    = self::CODE;
        $this->message = $message;
    }

    /**
     * Code of the error.
     *
     * @var string
     */
    protected $code;

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
        return $this->code;
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
