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
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryAmpVideo.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpStoryAmpVideo extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-story >> amp-video';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::VIDEO,
        SpecRule::SPEC_NAME => 'amp-story >> amp-video',
        SpecRule::ATTRS => [
            Attribute::AUTOPLAY => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::CONTROLS => [
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::DEPRECATION => '- no replacement',
                SpecRule::DEPRECATION_URL => 'https://github.com/ampproject/amphtml/issues/23798',
            ],
            '[controls]' => [
                SpecRule::DEPRECATION => '- no replacement',
                SpecRule::DEPRECATION_URL => 'https://github.com/ampproject/amphtml/issues/23798',
            ],
            Attribute::POSTER => [
                SpecRule::MANDATORY => true,
            ],
            Attribute::CACHE => [
                SpecRule::VALUE => [
                    'google',
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
            AttributeList\AmpVideoCommon::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-video/',
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
        SpecRule::MANDATORY_ANCESTOR => Extension::STORY,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::VIDEO,
        ],
    ];
}
