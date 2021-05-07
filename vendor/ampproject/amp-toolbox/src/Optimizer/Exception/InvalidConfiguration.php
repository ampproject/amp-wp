<?php

namespace AmpProject\Optimizer\Exception;

use DomainException;

/**
 * Exception thrown when an invalid configuration is provided, like in the case of mutually exclusive flags.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidConfiguration extends DomainException implements AmpOptimizerException
{

    /**
     * Instantiate an InvalidConfiguration exception for two mutually exclusive flags.
     *
     * @param string $flagA First flag that was used.
     * @param string $flagB Second flag that was used.
     * @return self
     */
    public static function forMutuallyExclusiveFlags($flagA, $flagB)
    {
        $message = "The configuration flags '{$flagA}' and '{$flagB}' are mutually exclusive "
                   . 'and cannot be set at the same time.';

        return new self($message);
    }
}
