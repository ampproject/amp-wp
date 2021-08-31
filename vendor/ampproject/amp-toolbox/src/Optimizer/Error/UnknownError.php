<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;

/**
 * Optimizer error object for when an unknown error has occurred.
 *
 * @package ampproject/amp-toolbox
 */
final class UnknownError implements Error
{
    use ErrorProperties;
}
