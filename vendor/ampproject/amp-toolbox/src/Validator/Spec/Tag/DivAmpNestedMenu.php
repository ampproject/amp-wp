<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class DivAmpNestedMenu.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array<string>> $attrs
 * @property-read array<string> $disallowedAncestor
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class DivAmpNestedMenu extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'div amp-nested-menu';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::DIV,
        SpecRule::SPEC_NAME => 'div amp-nested-menu',
        SpecRule::ATTRS => [
            Attribute::AMP_NESTED_SUBMENU => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::AMP_NESTED_SUBMENU,
                    Attribute::AMP_NESTED_SUBMENU_CLOSE,
                    Attribute::AMP_NESTED_SUBMENU_OPEN,
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
            ],
            Attribute::AMP_NESTED_SUBMENU_CLOSE => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::AMP_NESTED_SUBMENU,
                    Attribute::AMP_NESTED_SUBMENU_CLOSE,
                    Attribute::AMP_NESTED_SUBMENU_OPEN,
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
            ],
            Attribute::AMP_NESTED_SUBMENU_OPEN => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::AMP_NESTED_SUBMENU,
                    Attribute::AMP_NESTED_SUBMENU_CLOSE,
                    Attribute::AMP_NESTED_SUBMENU_OPEN,
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
            ],
        ],
        SpecRule::DISALLOWED_ANCESTOR => [
            'AMP-ACCORDION',
        ],
        SpecRule::MANDATORY_ANCESTOR => Extension::NESTED_MENU,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'div amp-nested-menu',
    ];
}
