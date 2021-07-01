<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Layout;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpAddthis.
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
final class AmpAddthis extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-ADDTHIS';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::ADDTHIS,
        SpecRule::ATTRS => [
            Attribute::DATA_PRODUCT_CODE => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_PRODUCT_CODE,
                    Attribute::DATA_WIDGET_ID,
                ],
            ],
            Attribute::DATA_SHARE_MEDIA => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                    SpecRule::ALLOW_EMPTY => true,
                ],
            ],
            Attribute::DATA_SHARE_URL => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                    SpecRule::ALLOW_EMPTY => true,
                ],
            ],
            Attribute::DATA_WIDGET_ID => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_PRODUCT_CODE,
                    Attribute::DATA_WIDGET_ID,
                ],
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_PUB_ID,
                    ],
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
            Extension::ADDTHIS,
        ],
    ];
}
