<?php

namespace AmpProject;

/**
 * Interface with constants for the different types of attributes.
 *
 * @package ampproject/common
 */
interface Attribute
{

    const ALT                   = 'alt';
    const AMP                   = 'amp';
    const AMP4ADS               = 'amp4ads';
    const AMP4ADS_BOILERPLATE   = 'amp4ads-boilerplate';
    const AMP4ADS_EMOJI         = self::AMP_EMOJI . '4ads';
    const AMP4ADS_EMOJI_ALT     = self::AMP_EMOJI_ALT . '4ads';
    const AMP4EMAIL             = 'amp4email';
    const AMP4EMAIL_BOILERPLATE = 'amp4email-boilerplate';
    const AMP4EMAIL_EMOJI       = self::AMP_EMOJI . '4email';
    const AMP4EMAIL_EMOJI_ALT   = self::AMP_EMOJI_ALT . '4email';
    const AMP_BOILERPLATE       = 'amp-boilerplate';
    const AMP_CUSTOM            = 'amp-custom';
    const AMP_EMOJI             = "\xE2\x9A\xA1";
    const AMP_EMOJI_ALT         = "\xE2\x9A\xA1\xEF\xB8\x8F"; // See https://github.com/ampproject/amphtml/issues/25990.
    const AMP_RUNTIME           = 'amp-runtime';
    const AMP_SCRIPT_SRC        = 'amp-script-src';
    const ARIA_HIDDEN           = 'aria-hidden';
    const ASYNC                 = 'async';
    const AUTOPLAY              = 'autoplay';
    const CHARSET               = 'charset';
    const CLASS_                = 'class'; // Underscore needed because 'class' is a PHP keyword.
    const CONTENT               = 'content';
    const CUSTOM_ELEMENT        = 'custom-element';
    const CUSTOM_TEMPLATE       = 'custom-template';
    const HEIGHT                = 'height';
    const HEIGHTS               = 'heights';
    const HIDDEN                = 'hidden';
    const HOST_SERVICE          = 'host-service';
    const HREF                  = 'href';
    const HTTP_EQUIV            = 'http-equiv';
    const I_AMPHTML_VERSION     = 'i-amphtml-version';
    const ID                    = 'id';
    const LAYOUT                = 'layout';
    const LOOP                  = 'loop';
    const MEDIA                 = 'media';
    const NAME                  = 'name';
    const ON                    = 'on';
    const PROFILE               = 'profile';
    const REL                   = 'rel';
    const ROLE                  = 'role';
    const SIZES                 = 'sizes';
    const SRC                   = 'src';
    const TABINDEX              = 'tabindex';
    const TEMPLATE              = 'template';
    const TYPE                  = 'type';
    const VIEWPORT              = 'viewport';
    const WIDTH                 = 'width';

    const ALL_AMP       = [self::AMP, self::AMP_EMOJI, self::AMP_EMOJI_ALT];
    const ALL_AMP4ADS   = [self::AMP4ADS, self::AMP4ADS_EMOJI, self::AMP4ADS_EMOJI_ALT];
    const ALL_AMP4EMAIL = [self::AMP4EMAIL, self::AMP4EMAIL_EMOJI, self::AMP4EMAIL_EMOJI_ALT];

    const ALL_BOILERPLATES = [self::AMP_BOILERPLATE, self::AMP4ADS_BOILERPLATE, self::AMP4EMAIL_BOILERPLATE];

    const TYPE_HTML       = 'text/html';
    const TYPE_JSON       = 'application/json';
    const TYPE_LD_JSON    = 'application/ld+json';
    const TYPE_TEXT_PLAIN = 'text/plain';

    const REL_CANONICAL    = 'canonical';
    const REL_DNS_PREFETCH = 'dns-prefetch';
    const REL_ICON         = 'icon';
    const REL_PRECONNECT   = 'preconnect';
    const REL_PREFETCH     = 'prefetch';
    const REL_PRELOAD      = 'preload';
    const REL_PRERENDER    = 'prerender';
    const REL_STYLESHEET   = 'stylesheet';
}
