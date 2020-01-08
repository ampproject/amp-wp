<?php

namespace Amp\Optimizer\Exception;

use InvalidArgumentException;

final class UnknownConfigurationKey extends InvalidArgumentException implements AmpOptimizerException
{

    /**
     * Instantiate an UnknownConfigurationKey exception for an unknown key.
     *
     * @param string $key Key that was unknown.
     * @return self
     */
    public static function fromKey($key)
    {
        $message = "The configuration does not contain the requested key '{$key}'";

        return new self($message);
    }
}
