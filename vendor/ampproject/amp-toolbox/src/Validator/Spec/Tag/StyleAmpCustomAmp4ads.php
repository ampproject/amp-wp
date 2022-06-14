<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\AtRule;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class StyleAmpCustomAmp4ads.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array $cdata
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class StyleAmpCustomAmp4ads extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'style amp-custom (AMP4ADS)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::STYLE,
        SpecRule::SPEC_NAME => 'style amp-custom (AMP4ADS)',
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::AMP_CUSTOM => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::TYPE => [
                SpecRule::VALUE_CASEI => [
                    'text/css',
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NonceAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/a4a_spec/#css',
        SpecRule::CDATA => [
            SpecRule::MAX_BYTES => 20000,
            SpecRule::MAX_BYTES_SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#maximum-size',
            SpecRule::DISALLOWED_CDATA_REGEX => [
                [
                    SpecRule::REGEX => '<!--',
                    SpecRule::ERROR_MESSAGE => 'html comments',
                ],
                [
                    SpecRule::REGEX => '(^|\W)i-amphtml-',
                    SpecRule::ERROR_MESSAGE => 'CSS i-amphtml- name prefix',
                ],
            ],
            SpecRule::CSS_SPEC => [
                SpecRule::AT_RULE_SPEC => [
                    [
                        SpecRule::NAME => AtRule::FONT_FACE,
                    ],
                    [
                        SpecRule::NAME => AtRule::KEYFRAMES,
                    ],
                    [
                        SpecRule::NAME => AtRule::MEDIA,
                        SpecRule::MEDIA_QUERY_SPEC => [
                            SpecRule::ISSUES_AS_ERROR => false,
                            SpecRule::TYPE => [
                                'all',
                                'print',
                                'screen',
                                'speech',
                            ],
                            SpecRule::FEATURE => [
                                'width',
                                'height',
                                'aspect-ratio',
                                'orientation',
                                'resolution',
                            ],
                        ],
                    ],
                    [
                        SpecRule::NAME => AtRule::SUPPORTS,
                    ],
                ],
                SpecRule::VALIDATE_AMP4ADS => true,
            ],
            SpecRule::DOC_CSS_BYTES => true,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4ADS,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'style amp-custom',
    ];
}
