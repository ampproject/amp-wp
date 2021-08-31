<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\DescendantTagList;

use AmpProject\Extension;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\DescendantTagList;
use AmpProject\Validator\Spec\Identifiable;

/**
 * Descendant tag list class AmpNestedMenuAllowedDescendants.
 *
 * @package ampproject/amp-toolbox.
 */
final class AmpNestedMenuAllowedDescendants extends DescendantTagList implements Identifiable
{
    /**
     * ID of the descendant tag list.
     *
     * @var string
     */
    const ID = 'amp-nested-menu-allowed-descendants';

    /**
     * Array of descendant tags.
     *
     * @var array<array>
     */
    const DESCENDANT_TAGS = [
        Element::A,
        Extension::ACCORDION,
        Extension::IMG,
        Extension::LIST_,
        Element::B,
        Element::BR,
        Element::BUTTON,
        Element::CIRCLE,
        Element::COL,
        Element::COLGROUP,
        Element::DIV,
        Element::ELLIPSE,
        Element::EM,
        Element::FIELDSET,
        Element::FORM,
        Element::H1,
        Element::H2,
        Element::H3,
        Element::H4,
        Element::H5,
        Element::H6,
        Element::I,
        Element::INPUT,
        Element::LABEL,
        Element::LI,
        Element::LINE,
        Element::MARK,
        Element::NAV,
        Element::OL,
        Element::OPTION,
        Element::P,
        Element::PATH,
        Element::POLYGON,
        Element::POLYLINE,
        Element::RECT,
        Element::SECTION,
        Element::SELECT,
        Element::SPAN,
        Element::STRIKE,
        Element::STRONG,
        Element::SUB,
        Element::SUP,
        Element::SVG,
        Element::TABLE,
        Element::TBODY,
        Element::TD,
        Element::TEMPLATE,
        Element::TH,
        Element::TIME,
        Element::TITLE,
        Element::TR,
        Element::U,
        Element::UL,
        Element::USE_,
    ];
}
