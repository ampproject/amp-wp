<?php

namespace AmpProject\Html\Parser;

/**
 * The html eflags, used internally by the parser.
 *
 * @package ampproject/amp-toolbox
 */
interface EFlags
{
    const OPTIONAL_ENDTAG   = 1;
    const EMPTY_            = 2;
    const CDATA             = 4;
    const RCDATA            = 8;
    const UNSAFE            = 16;
    const FOLDABLE          = 32;
    const UNKNOWN_OR_CUSTOM = 64;
}
