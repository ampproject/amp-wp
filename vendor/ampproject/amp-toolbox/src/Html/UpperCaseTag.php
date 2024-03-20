<?php

namespace AmpProject\Html;

/**
 * Interface with constants for the different types of tags.
 *
 * @package ampproject/amp-toolbox
 */
interface UpperCaseTag
{
    const A                   = 'A';
    const ABBR                = 'ABBR';
    const ACRONYM             = 'ACRONYM';
    const ADDRESS             = 'ADDRESS';
    const APPLET              = 'APPLET';
    const AREA                = 'AREA';
    const ARTICLE             = 'ARTICLE';
    const ASIDE               = 'ASIDE';
    const AUDIO               = 'AUDIO';
    const B                   = 'B';
    const BASE                = 'BASE';
    const BASEFONT            = 'BASEFONT';
    const BDI                 = 'BDI';
    const BDO                 = 'BDO';
    const BGSOUND             = 'BGSOUND';
    const BIG                 = 'BIG';
    const BLOCKQUOTE          = 'BLOCKQUOTE';
    const BODY                = 'BODY';
    const BR                  = 'BR';
    const BUTTON              = 'BUTTON';
    const CANVAS              = 'CANVAS';
    const CAPTION             = 'CAPTION';
    const CENTER              = 'CENTER';
    const CIRCLE              = 'CIRCLE';
    const CITE                = 'CITE';
    const CLIPPATH            = 'CLIPPATH';
    const CODE                = 'CODE';
    const COL                 = 'COL';
    const COLGROUP            = 'COLGROUP';
    const DATA                = 'DATA';
    const DATALIST            = 'DATALIST';
    const DD                  = 'DD';
    const DEFS                = 'DEFS';
    const DEL                 = 'DEL';
    const DESC                = 'DESC';
    const DETAILS             = 'DETAILS';
    const DFN                 = 'DFN';
    const DIR                 = 'DIR';
    const DIV                 = 'DIV';
    const DL                  = 'DL';
    const DT                  = 'DT';
    const ELLIPSE             = 'ELLIPSE';
    const EM                  = 'EM';
    const EMBED               = 'EMBED';
    const FEBLEND             = 'FEBLEND';
    const FECOLORMATRIX       = 'FECOLORMATRIX';
    const FECOMPONENTTRANSFER = 'FECOMPONENTTRANSFER';
    const FECOMPOSITE         = 'FECOMPOSITE';
    const FECONVOLVEMATRIX    = 'FECONVOLVEMATRIX';
    const FEDIFFUSELIGHTING   = 'FEDIFFUSELIGHTING';
    const FEDISPLACEMENTMAP   = 'FEDISPLACEMENTMAP';
    const FEDISTANTLIGHT      = 'FEDISTANTLIGHT';
    const FEDROPSHADOW        = 'FEDROPSHADOW';
    const FEFLOOD             = 'FEFLOOD';
    const FEFUNCA             = 'FEFUNCA';
    const FEFUNCB             = 'FEFUNCB';
    const FEFUNCG             = 'FEFUNCG';
    const FEFUNCR             = 'FEFUNCR';
    const FEGAUSSIANBLUR      = 'FEGAUSSIANBLUR';
    const FEMERGE             = 'FEMERGE';
    const FEMERGENODE         = 'FEMERGENODE';
    const FEMORPHOLOGY        = 'FEMORPHOLOGY';
    const FEOFFSET            = 'FEOFFSET';
    const FEPOINTLIGHT        = 'FEPOINTLIGHT';
    const FESPECULARLIGHTING  = 'FESPECULARLIGHTING';
    const FESPOTLIGHT         = 'FESPOTLIGHT';
    const FETILE              = 'FETILE';
    const FETURBULENCE        = 'FETURBULENCE';
    const FIELDSET            = 'FIELDSET';
    const FIGCAPTION          = 'FIGCAPTION';
    const FIGURE              = 'FIGURE';
    const FILTER              = 'FILTER';
    const FONT                = 'FONT';
    const FOOTER              = 'FOOTER';
    const FORM                = 'FORM';
    const FRAME               = 'FRAME';
    const FRAMESET            = 'FRAMESET';
    const G                   = 'G';
    const GLYPH               = 'GLYPH';
    const GLYPHREF            = 'GLYPHREF';
    const H1                  = 'H1';
    const H2                  = 'H2';
    const H3                  = 'H3';
    const H4                  = 'H4';
    const H5                  = 'H5';
    const H6                  = 'H6';
    const HEAD                = 'HEAD';
    const HEADER              = 'HEADER';
    const HGROUP              = 'HGROUP';
    const HKERN               = 'HKERN';
    const HR                  = 'HR';
    const HTML                = 'HTML';
    const I                   = 'I';
    const IFRAME              = 'IFRAME';
    const IMAGE               = 'IMAGE';
    const IMG                 = 'IMG';
    const INPUT               = 'INPUT';
    const INS                 = 'INS';
    const ISINDEX             = 'ISINDEX';
    const KBD                 = 'KBD';
    const KEYGEN              = 'KEYGEN';
    const LABEL               = 'LABEL';
    const LEGEND              = 'LEGEND';
    const LI                  = 'LI';
    const LINE                = 'LINE';
    const LINEARGRADIENT      = 'LINEARGRADIENT';
    const LINK                = 'LINK';
    const LISTING             = 'LISTING';
    const MAIN                = 'MAIN';
    const MAP                 = 'MAP';
    const MARK                = 'MARK';
    const MARKER              = 'MARKER';
    const MASK                = 'MASK';
    const MENU                = 'MENU';
    const META                = 'META';
    const METADATA            = 'METADATA';
    const METER               = 'METER';
    const MULTICOL            = 'MULTICOL';
    const NAV                 = 'NAV';
    const NEXTID              = 'NEXTID';
    const NOBR                = 'NOBR';
    const NOFRAMES            = 'NOFRAMES';
    const NOSCRIPT            = 'NOSCRIPT';
    const O_P                 = 'O:P'; // @TODO WILL THIS BE USABLE AT PRESENT GIVEN PHP DOM?
    const OBJECT              = 'OBJECT';
    const OL                  = 'OL';
    const OPTGROUP            = 'OPTGROUP';
    const OPTION              = 'OPTION';
    const OUTPUT              = 'OUTPUT';
    const P                   = 'P';
    const PARAM               = 'PARAM';
    const PATH                = 'PATH';
    const PATTERN             = 'PATTERN';
    const PICTURE             = 'PICTURE';
    const POLYGON             = 'POLYGON';
    const POLYLINE            = 'POLYLINE';
    const PRE                 = 'PRE';
    const PROGRESS            = 'PROGRESS';
    const Q                   = 'Q';
    const RADIALGRADIENT      = 'RADIALGRADIENT';
    const RB                  = 'RB';
    const RECT                = 'RECT';
    const RP                  = 'RP';
    const RT                  = 'RT';
    const RTC                 = 'RTC';
    const RUBY                = 'RUBY';
    const S                   = 'S';
    const SAMP                = 'SAMP';
    const SCRIPT              = 'SCRIPT';
    const SECTION             = 'SECTION';
    const SELECT              = 'SELECT';
    const SLOT                = 'SLOT';
    const SMALL               = 'SMALL';
    const SOLIDCOLOR          = 'SOLIDCOLOR';
    const SOURCE              = 'SOURCE';
    const SPACER              = 'SPACER';
    const SPAN                = 'SPAN';
    const STOP                = 'STOP';
    const STRIKE              = 'STRIKE';
    const STRONG              = 'STRONG';
    const STYLE               = 'STYLE';
    const SUB                 = 'SUB';
    const SUMMARY             = 'SUMMARY';
    const SUP                 = 'SUP';
    const SVG                 = 'SVG';
    const SWITCH_             = 'SWITCH';
    const SYMBOL              = 'SYMBOL';
    const TABLE               = 'TABLE';
    const TBODY               = 'TBODY';
    const TD                  = 'TD';
    const TEMPLATE            = 'TEMPLATE';
    const TEXT                = 'TEXT';
    const TEXTAREA            = 'TEXTAREA';
    const TEXTPATH            = 'TEXTPATH';
    const TFOOT               = 'TFOOT';
    const TH                  = 'TH';
    const THEAD               = 'THEAD';
    const TIME                = 'TIME';
    const TITLE               = 'TITLE';
    const TR                  = 'TR';
    const TRACK               = 'TRACK';
    const TREF                = 'TREF';
    const TSPAN               = 'TSPAN';
    const TT                  = 'TT';
    const U                   = 'U';
    const UL                  = 'UL';
    const USE_                = 'USE';
    const VAR_                = 'VAR';
    const VIDEO               = 'VIDEO';
    const VIEW                = 'VIEW';
    const VKERN               = 'VKERN';
    const WBR                 = 'WBR';
    const _DOCTYPE            = '!DOCTYPE';

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
