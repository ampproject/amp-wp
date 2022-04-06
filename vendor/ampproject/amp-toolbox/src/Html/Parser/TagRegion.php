<?php

namespace AmpProject\Html\Parser;

use AmpProject\FakeEnum;

/**
 * Enum for denoting to which structural region a tag belongs.
 *
 *
 * @method static TagRegion PRE_DOCTYPE()
 * @method static TagRegion PRE_HTML()
 * @method static TagRegion PRE_HEAD()
 * @method static TagRegion IN_HEAD()
 * @method static TagRegion PRE_BODY()
 * @method static TagRegion IN_BODY()
 * @method static TagRegion IN_SVG()
 *
 * @package ampproject/amp-toolbox
 */
final class TagRegion extends FakeEnum
{
    const PRE_DOCTYPE = 0;
    const PRE_HTML    = 1;
    const PRE_HEAD    = 2;
    const IN_HEAD     = 3;
    const PRE_BODY    = 4; // After closing <head> tag, but before open <body> tag.
    const IN_BODY     = 5;
    const IN_SVG      = 6;

    // We don't track the region after the closing body tag.
}
