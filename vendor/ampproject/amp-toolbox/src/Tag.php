<?php

namespace AmpProject;

/**
 * Interface with constants for the different types of tags.
 *
 * @package ampproject/amp-toolbox
 */
interface Tag
{

    const A          = 'a';
    const AREA       = 'area';
    const BASE       = 'base';
    const BASEFONT   = 'basefont';
    const BGSOUND    = 'bgsound';
    const BODY       = 'body';
    const BR         = 'br';
    const COL        = 'col';
    const DIV        = 'div';
    const EMBED      = 'embed';
    const FIGCAPTION = 'figcaption';
    const FIGURE     = 'figure';
    const FORM       = 'form';
    const FRAME      = 'frame';
    const HEAD       = 'head';
    const HR         = 'hr';
    const HTML       = 'html';
    const IMG        = 'img';
    const INPUT      = 'input';
    const KEYGEN     = 'keygen';
    const LINK       = 'link';
    const META       = 'meta';
    const NOSCRIPT   = 'noscript';
    const OBJECT     = 'object';
    const P          = 'p';
    const PARAM      = 'param';
    const SCRIPT     = 'script';
    const SOURCE     = 'source';
    const STYLE      = 'style';
    const TEMPLATE   = 'template';
    const TITLE      = 'title';
    const TRACK      = 'track';
    const WBR        = 'wbr';

    /**
     * HTML elements that are self-closing.
     *
     * @link https://www.w3.org/TR/html5/syntax.html#serializing-html-fragments
     *
     * @var string[]
     */
    const SELF_CLOSING_TAGS = [
        self::AREA,
        self::BASE,
        self::BASEFONT,
        self::BGSOUND,
        self::BR,
        self::COL,
        self::EMBED,
        self::FRAME,
        self::HR,
        self::IMG,
        self::INPUT,
        self::KEYGEN,
        self::LINK,
        self::META,
        self::PARAM,
        self::SOURCE,
        self::TRACK,
        self::WBR,
    ];

    /**
     * List of elements allowed in head.
     *
     * @link https://github.com/ampproject/amphtml/blob/445d6e3be8a5063e2738c6f90fdcd57f2b6208be/validator/engine/htmlparser.js#L83-L100
     * @link https://www.w3.org/TR/html5/document-metadata.html
     *
     * @var string[]
     */
    const ELEMENTS_ALLOWED_IN_HEAD = [
        self::TITLE,
        self::BASE,
        self::LINK,
        self::META,
        self::STYLE,
        self::NOSCRIPT,
        self::SCRIPT,
    ];
}
