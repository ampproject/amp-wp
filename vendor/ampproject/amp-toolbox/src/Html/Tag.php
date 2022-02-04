<?php

namespace AmpProject\Html;

/**
 * Interface with constants for the different types of tags.
 *
 * @package ampproject/amp-toolbox
 */
interface Tag
{
    const A                   = 'a';
    const ABBR                = 'abbr';
    const ACRONYM             = 'acronym';
    const ADDRESS             = 'address';
    const APPLET              = 'applet';
    const AREA                = 'area';
    const ARTICLE             = 'article';
    const ASIDE               = 'aside';
    const AUDIO               = 'audio';
    const B                   = 'b';
    const BASE                = 'base';
    const BASEFONT            = 'basefont';
    const BDI                 = 'bdi';
    const BDO                 = 'bdo';
    const BGSOUND             = 'bgsound';
    const BIG                 = 'big';
    const BLOCKQUOTE          = 'blockquote';
    const BODY                = 'body';
    const BR                  = 'br';
    const BUTTON              = 'button';
    const CANVAS              = 'canvas';
    const CAPTION             = 'caption';
    const CENTER              = 'center';
    const CIRCLE              = 'circle';
    const CITE                = 'cite';
    const CLIPPATH            = 'clippath';
    const CODE                = 'code';
    const COL                 = 'col';
    const COLGROUP            = 'colgroup';
    const DATA                = 'data';
    const DATALIST            = 'datalist';
    const DD                  = 'dd';
    const DEFS                = 'defs';
    const DEL                 = 'del';
    const DESC                = 'desc';
    const DETAILS             = 'details';
    const DFN                 = 'dfn';
    const DIR                 = 'dir';
    const DIV                 = 'div';
    const DL                  = 'dl';
    const DT                  = 'dt';
    const ELLIPSE             = 'ellipse';
    const EM                  = 'em';
    const EMBED               = 'embed';
    const FEBLEND             = 'feblend';
    const FECOLORMATRIX       = 'fecolormatrix';
    const FECOMPONENTTRANSFER = 'fecomponenttransfer';
    const FECOMPOSITE         = 'fecomposite';
    const FECONVOLVEMATRIX    = 'feconvolvematrix';
    const FEDIFFUSELIGHTING   = 'fediffuselighting';
    const FEDISPLACEMENTMAP   = 'fedisplacementmap';
    const FEDISTANTLIGHT      = 'fedistantlight';
    const FEDROPSHADOW        = 'fedropshadow';
    const FEFLOOD             = 'feflood';
    const FEFUNCA             = 'fefunca';
    const FEFUNCB             = 'fefuncb';
    const FEFUNCG             = 'fefuncg';
    const FEFUNCR             = 'fefuncr';
    const FEGAUSSIANBLUR      = 'fegaussianblur';
    const FEMERGE             = 'femerge';
    const FEMERGENODE         = 'femergenode';
    const FEMORPHOLOGY        = 'femorphology';
    const FEOFFSET            = 'feoffset';
    const FEPOINTLIGHT        = 'fepointlight';
    const FESPECULARLIGHTING  = 'fespecularlighting';
    const FESPOTLIGHT         = 'fespotlight';
    const FETILE              = 'fetile';
    const FETURBULENCE        = 'feturbulence';
    const FIELDSET            = 'fieldset';
    const FIGCAPTION          = 'figcaption';
    const FIGURE              = 'figure';
    const FILTER              = 'filter';
    const FONT                = 'font';
    const FOOTER              = 'footer';
    const FORM                = 'form';
    const FRAME               = 'frame';
    const FRAMESET            = 'frameset';
    const G                   = 'g';
    const GLYPH               = 'glyph';
    const GLYPHREF            = 'glyphref';
    const H1                  = 'h1';
    const H2                  = 'h2';
    const H3                  = 'h3';
    const H4                  = 'h4';
    const H5                  = 'h5';
    const H6                  = 'h6';
    const HEAD                = 'head';
    const HEADER              = 'header';
    const HGROUP              = 'hgroup';
    const HKERN               = 'hkern';
    const HR                  = 'hr';
    const HTML                = 'html';
    const I                   = 'i';
    const IFRAME              = 'iframe';
    const IMAGE               = 'image';
    const IMG                 = 'img';
    const INPUT               = 'input';
    const INS                 = 'ins';
    const ISINDEX             = 'isindex';
    const KBD                 = 'kbd';
    const KEYGEN              = 'keygen';
    const LABEL               = 'label';
    const LEGEND              = 'legend';
    const LI                  = 'li';
    const LINE                = 'line';
    const LINEARGRADIENT      = 'lineargradient';
    const LINK                = 'link';
    const LISTING             = 'listing';
    const MAIN                = 'main';
    const MAP                 = 'map';
    const MARK                = 'mark';
    const MARKER              = 'marker';
    const MASK                = 'mask';
    const MENU                = 'menu';
    const META                = 'meta';
    const METADATA            = 'metadata';
    const METER               = 'meter';
    const MULTICOL            = 'multicol';
    const NAV                 = 'nav';
    const NEXTID              = 'nextid';
    const NOBR                = 'nobr';
    const NOFRAMES            = 'noframes';
    const NOSCRIPT            = 'noscript';
    const O_P                 = 'o:p'; // @todo Will this be usable at present given PHP DOM?
    const OBJECT              = 'object';
    const OL                  = 'ol';
    const OPTGROUP            = 'optgroup';
    const OPTION              = 'option';
    const OUTPUT              = 'output';
    const P                   = 'p';
    const PARAM               = 'param';
    const PATH                = 'path';
    const PATTERN             = 'pattern';
    const PICTURE             = 'picture';
    const POLYGON             = 'polygon';
    const POLYLINE            = 'polyline';
    const PRE                 = 'pre';
    const PROGRESS            = 'progress';
    const Q                   = 'Q';
    const RADIALGRADIENT      = 'radialgradient';
    const RB                  = 'rb';
    const RECT                = 'rect';
    const RP                  = 'rp';
    const RT                  = 'rt';
    const RTC                 = 'rtc';
    const RUBY                = 'ruby';
    const S                   = 's';
    const SAMP                = 'samp';
    const SCRIPT              = 'script';
    const SECTION             = 'section';
    const SELECT              = 'select';
    const SLOT                = 'slot';
    const SMALL               = 'small';
    const SOLIDCOLOR          = 'solidcolor';
    const SOURCE              = 'source';
    const SPACER              = 'spacer';
    const SPAN                = 'span';
    const STOP                = 'stop';
    const STRIKE              = 'strike';
    const STRONG              = 'strong';
    const STYLE               = 'style';
    const SUB                 = 'sub';
    const SUMMARY             = 'summary';
    const SUP                 = 'sup';
    const SVG                 = 'svg';
    const SWITCH_             = 'switch';
    const SYMBOL              = 'symbol';
    const TABLE               = 'table';
    const TBODY               = 'tbody';
    const TD                  = 'td';
    const TEMPLATE            = 'template';
    const TEXT                = 'text';
    const TEXTAREA            = 'textarea';
    const TEXTPATH            = 'textpath';
    const TFOOT               = 'tfoot';
    const TH                  = 'th';
    const THEAD               = 'thead';
    const TIME                = 'time';
    const TITLE               = 'title';
    const TR                  = 'tr';
    const TRACK               = 'track';
    const TREF                = 'tref';
    const TSPAN               = 'tspan';
    const TT                  = 'tt';
    const U                   = 'u';
    const UL                  = 'ul';
    const USE_                = 'use';
    const VAR_                = 'var';
    const VIDEO               = 'video';
    const VIEW                = 'view';
    const VKERN               = 'vkern';
    const WBR                 = 'wbr';
    const _DOCTYPE            = '!doctype';

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

    /**
     * Set of HTML tags which should never trigger an implied open of a <head> or <body> element.
     */
    const STRUCTURE_TAGS = [
        self::_DOCTYPE,
        self::HTML,
        self::HEAD,
        self::BODY,
    ];

    /**
     * The set of HTML tags whose presence will implicitly close a <p> element.
     * For example '<p>foo<h1>bar</h1>' should parse the same as '<p>foo</p><h1>bar</h1>'.
     * @link https://www.w3.org/TR/html-markup/p.html
     */
    const P_CLOSING_TAGS = [
        self::ADDRESS,
        self::ARTICLE,
        self::ASIDE,
        self::BLOCKQUOTE,
        self::DIR,
        self::DL,
        self::FIELDSET,
        self::FOOTER,
        self::FORM,
        self::H1,
        self::H2,
        self::H3,
        self::H4,
        self::H5,
        self::H6,
        self::HEADER,
        self::HR,
        self::MENU,
        self::NAV,
        self::OL,
        self::P,
        self::PRE,
        self::SECTION,
        self::TABLE,
        self::UL,
    ];
}
