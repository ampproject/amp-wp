<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\AtRule;
use AmpProject\Attribute;
use AmpProject\Format;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class StyleAmpCustomCssStrict.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read string $specUrl
 * @property-read array $cdata
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $enabledBy
 * @property-read string $descriptiveName
 */
final class StyleAmpCustomCssStrict extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'style amp-custom (css-strict)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::STYLE,
        SpecRule::SPEC_NAME => 'style amp-custom (css-strict)',
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
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/email-spec/amp-email-css',
        SpecRule::CDATA => [
            SpecRule::MAX_BYTES => 75000,
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
                        SpecRule::NAME => AtRule::MEDIA,
                        SpecRule::MEDIA_QUERY_SPEC => [
                            SpecRule::ISSUES_AS_ERROR => true,
                            SpecRule::TYPE => [
                                'all',
                                'screen',
                            ],
                            SpecRule::FEATURE => [
                                'device-width',
                                'hover',
                                'max-device-width',
                                'max-resolution',
                                'max-width',
                                'min-device-width',
                                'min-resolution',
                                'min-width',
                                'orientation',
                                'pointer',
                                'resolution',
                                'width',
                            ],
                        ],
                    ],
                ],
                SpecRule::SELECTOR_SPEC => [
                    SpecRule::ATTRIBUTE_NAME => [
                        'active',
                        'alt',
                        'autocomplete',
                        'autoexpand',
                        'checked',
                        'class',
                        'controls',
                        'dir',
                        'disabled',
                        'expanded',
                        'fallback',
                        'fetch-error',
                        'height',
                        'hidden',
                        'id',
                        'items',
                        'layout',
                        'name',
                        'noloading',
                        'novalidate',
                        'open',
                        'option',
                        'overflow',
                        'placeholder',
                        'readonly',
                        'required',
                        'role',
                        'scrollable',
                        'selected',
                        'side',
                        'sizes',
                        'submit-error',
                        'submit-success',
                        'submitting',
                        'title',
                        'type',
                        'value',
                        'width',
                    ],
                    SpecRule::PSEUDO_CLASS => [
                        'active',
                        'checked',
                        'default',
                        'disabled',
                        'empty',
                        'enabled',
                        'first-child',
                        'first-of-type',
                        'focus',
                        'focus-within',
                        'hover',
                        'in-range',
                        'indeterminate',
                        'invalid',
                        'last-child',
                        'last-of-type',
                        'not',
                        'nth-child',
                        'nth-last-child',
                        'nth-last-of-type',
                        'nth-of-type',
                        'only-child',
                        'only-of-type',
                        'optional',
                        'out-of-range',
                        'read-only',
                        'read-write',
                        'required',
                        'valid',
                    ],
                ],
            ],
            SpecRule::DOC_CSS_BYTES => true,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4EMAIL,
        ],
        SpecRule::ENABLED_BY => [
            Attribute::DATA_CSS_STRICT,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'style amp-custom',
    ];
}
