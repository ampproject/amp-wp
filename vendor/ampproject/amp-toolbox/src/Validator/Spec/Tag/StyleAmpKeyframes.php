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
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class StyleAmpKeyframes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array $cdata
 * @property-read array<string> $htmlFormat
 * @property-read bool $mandatoryLastChild
 * @property-read string $descriptiveName
 */
final class StyleAmpKeyframes extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'style[amp-keyframes]';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::STYLE,
        SpecRule::SPEC_NAME => 'style[amp-keyframes]',
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::BODY,
        SpecRule::ATTRS => [
            Attribute::AMP_KEYFRAMES => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_DISPATCH',
            ],
        ],
        SpecRule::CDATA => [
            SpecRule::MAX_BYTES => 500000,
            SpecRule::MAX_BYTES_SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#keyframes-stylesheet',
            SpecRule::CSS_SPEC => [
                SpecRule::AT_RULE_SPEC => [
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
                                'tty',
                                'tv',
                                'projection',
                                'handheld',
                                'braille',
                                'embossesd',
                                'aural',
                                '-sass-debug-info',
                                'device-pixel-ratio',
                                'device-pixel-ratio2',
                            ],
                            SpecRule::FEATURE => [
                                'any-hover',
                                'any-pointer',
                                'aspect-ratio',
                                'color',
                                'color-gamut',
                                'color-index',
                                'device-aspect-ratio',
                                'device-height',
                                'device-width',
                                'display-mode',
                                'forced-colors',
                                'grid',
                                'height',
                                'hover',
                                'inverted-colors',
                                'light-level',
                                'monochrome',
                                'max-aspect-ratio',
                                'max-color-index',
                                'max-device-aspect-ratio',
                                'max-device-height',
                                'max-device-width',
                                'max-height',
                                'max-resolution',
                                'max-width',
                                'min-aspect-ratio',
                                'min-color-index',
                                'min-device-aspect-ratio',
                                'min-device-height',
                                'min-device-width',
                                'min-height',
                                'min-resolution',
                                'min-width',
                                'orientation',
                                'overflow-block',
                                'overflow-inline',
                                'pointer',
                                'prefers-color-scheme',
                                'prefers-contrast',
                                'prefers-reduced-motion',
                                'prefers-reduced-transparency',
                                'resolution',
                                'scan',
                                'scripting',
                                'transform-3d',
                                'update',
                                'width',
                                '--mod',
                                '--md',
                                'device-pixel-ratio',
                                'device-pixel-ratio2',
                                'high-contrast',
                                'aspect-ratio',
                                'max-device-pixel-ratio',
                                'min-device-pixel-ratio',
                                'max-device-pixel-ratio2',
                                'min-device-pixel-ratio2',
                            ],
                        ],
                    ],
                    [
                        SpecRule::NAME => AtRule::SUPPORTS,
                    ],
                ],
                SpecRule::VALIDATE_KEYFRAMES => true,
                SpecRule::DECLARATION => [
                    'animation-timing-function',
                    'offset-distance',
                    'opacity',
                    'transform',
                    'visibility',
                ],
            ],
            SpecRule::DOC_CSS_BYTES => false,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
        ],
        SpecRule::MANDATORY_LAST_CHILD => true,
        SpecRule::DESCRIPTIVE_NAME => 'style[amp-keyframes]',
    ];
}
