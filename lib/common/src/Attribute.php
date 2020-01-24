<?php

namespace Amp;

/**
 * Interface with constants for the different types of attributes.
 *
 * @package amp/common
 */
interface Attribute
{

    const ASYNC           = 'async';
    const CUSTOM_ELEMENT  = 'custom-element';
    const CUSTOM_TEMPLATE = 'custom-template';
    const HOST_SERVICE    = 'host-service';
    const HREF            = 'href';
    const SRC             = 'src';
}
