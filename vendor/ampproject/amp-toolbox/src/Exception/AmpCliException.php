<?php

namespace AmpProject\Exception;

/**
 * Marker interface to distinguish exceptions for the CLI.
 *
 * @package ampproject/amp-toolbox
 */
interface AmpCliException extends AmpException
{

    /**
     * No error code specified.
     *
     * @var int
     */
    const E_ANY = -1;

    /**
     * Command is not valid.
     *
     * @var int
     */
    const E_INVALID_CMD = 6;

    /**
     * Could not read or parse arguments.
     *
     * @var int
     */
    const E_ARG_READ = 5;

    /**
     * Option requires an argument.
     *
     * @var int
     */
    const E_OPT_ABIGUOUS = 4;

    /**
     * Argument not allowed for option.
     *
     * @var int
     */
    const E_OPT_ARG_DENIED = 3;

    /**
     * Option ambiguous.
     *
     * @var int
     */
    const E_OPT_ARG_REQUIRED = 2;

    /**
     * Option unknown.
     *
     * @var int
     */
    const E_UNKNOWN_OPT = 1;
}
