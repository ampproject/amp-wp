<?php

namespace Amp;

/**
 * Interface with constants for the different types of attributes.
 *
 * @package amp/common
 */
interface Attribute
{

    const AMP4ADS_BOILERPLATE = 'amp4ads-boilerplate';
    const AMP_BOILERPLATE     = 'amp-boilerplate';
    const AMP_CUSTOM          = 'amp-custom';
    const AMP_RUNTIME         = 'amp-runtime';
    const ASYNC               = 'async';
    const CHARSET             = 'charset';
    const CLASS_              = 'class'; // Underscore needed because 'class' is a PHP keyword.
    const CONTENT             = 'content';
    const CUSTOM_ELEMENT      = 'custom-element';
    const CUSTOM_TEMPLATE     = 'custom-template';
    const HEIGHT              = 'height';
    const HEIGHTS             = 'heights';
    const HIDDEN              = 'hidden';
    const HOST_SERVICE        = 'host-service';
    const HREF                = 'href';
    const HTTP_EQUIV          = 'http-equiv';
    const I_AMPHTML_VERSION   = 'i-amphtml-version';
    const LAYOUT              = 'layout';
    const MEDIA               = 'media';
    const REL                 = 'rel';
    const SIZES               = 'sizes';
    const SRC                 = 'src';
    const TYPE                = 'type';
    const WIDTH               = 'width';

    const TYPE_JSON = 'application/json';
}
