<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\DescendantTagList;

use AmpProject\Extension;
use AmpProject\Html\Tag as Element;
use AmpProject\Internal;
use AmpProject\Validator\Spec\DescendantTagList;
use AmpProject\Validator\Spec\Identifiable;

/**
 * Descendant tag list class AmpStoryCtaLayerAllowedDescendants.
 *
 * @package ampproject/amp-toolbox.
 */
final class AmpStoryCtaLayerAllowedDescendants extends DescendantTagList implements Identifiable
{
    /**
     * ID of the descendant tag list.
     *
     * @var string
     */
    const ID = 'amp-story-cta-layer-allowed-descendants';

    /**
     * Array of descendant tags.
     *
     * @var array<string>
     */
    const DESCENDANT_TAGS = [
        Element::A,
        Element::ABBR,
        Element::ADDRESS,
        Extension::CALL_TRACKING,
        Extension::DATE_COUNTDOWN,
        Extension::DATE_DISPLAY,
        Extension::FIT_TEXT,
        Extension::FONT,
        Extension::IMG,
        Extension::TIMEAGO,
        Element::B,
        Element::BDI,
        Element::BDO,
        Element::BLOCKQUOTE,
        Element::BR,
        Element::BUTTON,
        Element::CAPTION,
        Element::CITE,
        Element::CIRCLE,
        Element::CLIPPATH,
        Element::CODE,
        Element::DATA,
        Element::DEFS,
        Element::DEL,
        Element::DESC,
        Element::DFN,
        Element::DIV,
        Element::ELLIPSE,
        Element::EM,
        Element::FECOLORMATRIX,
        Element::FECOMPOSITE,
        Element::FEBLEND,
        Element::FEFLOOD,
        Element::FEGAUSSIANBLUR,
        Element::FEMERGE,
        Element::FEMERGENODE,
        Element::FEOFFSET,
        Element::FIGCAPTION,
        Element::FIGURE,
        Element::FILTER,
        Element::FOOTER,
        Element::G,
        Element::GLYPH,
        Element::GLYPHREF,
        Element::H1,
        Element::H2,
        Element::H3,
        Element::H4,
        Element::H5,
        Element::H6,
        Element::HEADER,
        Element::HGROUP,
        Element::HKERN,
        Element::HR,
        Element::I,
        Element::IMG,
        Internal::SIZER,
        Element::IMAGE,
        Element::INS,
        Element::KBD,
        Element::LI,
        Element::LINE,
        Element::LINEARGRADIENT,
        Element::MAIN,
        Element::MARKER,
        Element::MARK,
        Element::MASK,
        Element::METADATA,
        Element::NAV,
        Element::NOSCRIPT,
        Element::OL,
        Element::P,
        Element::PATH,
        Element::PATTERN,
        Element::PRE,
        Element::POLYGON,
        Element::POLYLINE,
        Element::RADIALGRADIENT,
        Element::Q,
        Element::RECT,
        Element::RP,
        Element::RT,
        Element::RTC,
        Element::RUBY,
        Element::S,
        Element::SAMP,
        Element::SECTION,
        Element::SMALL,
        Element::SOLIDCOLOR,
        Element::SPAN,
        Element::STOP,
        Element::STRONG,
        Element::SUB,
        Element::SUP,
        Element::SVG,
        Element::SWITCH_,
        Element::SYMBOL,
        Element::TEXT,
        Element::TEXTPATH,
        Element::TREF,
        Element::TSPAN,
        Element::TITLE,
        Element::TIME,
        Element::TR,
        Element::U,
        Element::UL,
        Element::USE_,
        Element::VAR_,
        Element::VIEW,
        Element::VKERN,
        Element::WBR,
    ];
}
