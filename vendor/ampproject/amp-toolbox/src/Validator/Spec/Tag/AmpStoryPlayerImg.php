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
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryPlayerImg.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class AmpStoryPlayerImg extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-story-player > img';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::IMG,
        SpecRule::SPEC_NAME => 'amp-story-player > img',
        SpecRule::MANDATORY_PARENT => Element::A,
        SpecRule::ATTRS => [
            Attribute::ALT => [],
            Attribute::ATTRIBUTION => [],
            Attribute::DATA_AMP_STORY_PLAYER_POSTER_IMG => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
            ],
            Attribute::DECODING => [
                SpecRule::VALUE => [
                    'async',
                ],
            ],
            Attribute::HEIGHT => [
                SpecRule::VALUE_REGEX => '[0-9]+',
            ],
            Attribute::LOADING => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'lazy',
                ],
            ],
            Attribute::SIZES => [],
            Attribute::WIDTH => [
                SpecRule::VALUE_REGEX => '[0-9]+',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\MandatorySrcOrSrcset::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-story-player/',
        SpecRule::MANDATORY_ANCESTOR => Extension::STORY_PLAYER,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
    ];
}
