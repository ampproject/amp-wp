<?php

namespace AmpProject\Optimizer\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an unknown configuration key was requested.
 *
 * @package ampproject/amp-toolbox
 */
final class UnknownConfigurationClass extends InvalidArgumentException implements AmpOptimizerException
{
    /**
     * Instantiate an UnknownConfigurationClass exception for an unknown configuration class.
     *
     * @param string $transformerClass Key that was unknown.
     * @return self
     */
    public static function fromTransformerClass($transformerClass)
    {
        $message = "No configuration class was registered for the transformer '{$transformerClass}'.";

        return new self($message);
    }
}
