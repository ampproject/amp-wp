<?php

namespace Amp;

/**
 * Interface with constants for the different types of tags.
 *
 * @package amp/common
 */
interface Tag
{

    const BODY     = 'body';
    const HEAD     = 'head';
    const HTML     = 'html';
    const LINK     = 'link';
    const META     = 'meta';
    const SCRIPT   = 'script';
    const STYLE    = 'style';
    const TEMPLATE = 'template';
}
