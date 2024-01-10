<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Layout;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpList.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpList extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-LIST';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::LIST_,
        SpecRule::ATTRS => [
            Attribute::AUTO_RESIZE => [
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::DEPRECATION => 'replacement-to-be-determined-at-a-later-date',
                SpecRule::DEPRECATION_URL => 'https://github.com/ampproject/amphtml/issues/18849',
            ],
            Attribute::BINDING => [
                SpecRule::VALUE => [
                    'always',
                    'no',
                    'refresh',
                ],
            ],
            Attribute::CREDENTIALS => [],
            Attribute::DATA_AMP_BIND_SRC => [
                SpecRule::MANDATORY_ANYOF => [
                    Attribute::SRC,
                    '[src]',
                    Attribute::DATA_AMP_BIND_SRC,
                ],
            ],
            Attribute::DIFFABLE => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::ITEMS => [],
            Attribute::LOAD_MORE => [
                SpecRule::VALUE => [
                    'auto',
                    'manual',
                ],
            ],
            Attribute::LOAD_MORE_BOOKMARK => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::LOAD_MORE,
                    ],
                ],
            ],
            Attribute::MAX_ITEMS => [],
            Attribute::RESET_ON_REFRESH => [
                SpecRule::VALUE => [
                    '',
                    'always',
                    'fetch',
                ],
            ],
            Attribute::SINGLE_ITEM => [],
            Attribute::SRC => [
                SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTPS,
                        Protocol::AMP_STATE,
                        Protocol::AMP_SCRIPT,
                    ],
                    SpecRule::ALLOW_RELATIVE => true,
                ],
                SpecRule::MANDATORY_ANYOF => [
                    Attribute::SRC,
                    '[src]',
                    Attribute::DATA_AMP_BIND_SRC,
                ],
            ],
            Attribute::TEMPLATE => [
                SpecRule::VALUE_ONEOF_SET => 'TEMPLATE_IDS',
            ],
            Attribute::XSSI_PREFIX => [],
            '[is-layout-container]' => [],
            '[src]' => [
                SpecRule::MANDATORY_ANYOF => [
                    Attribute::SRC,
                    '[src]',
                    Attribute::DATA_AMP_BIND_SRC,
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::FILL,
                Layout::FIXED,
                Layout::FIXED_HEIGHT,
                Layout::FLEX_ITEM,
                Layout::NODISPLAY,
                Layout::RESPONSIVE,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::LIST_,
        ],
    ];
}
