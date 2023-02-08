<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Layout;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpAdExit.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read array $childTags
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requires
 * @property-read array<string> $requiresExtension
 */
final class AmpAdExit extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-AD-EXIT';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::AD_EXIT,
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
            AttributeList\MandatoryIdAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-ad-exit/',
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::NODISPLAY,
                Layout::CONTAINER,
            ],
        ],
        SpecRule::CHILD_TAGS => [
            SpecRule::MANDATORY_NUM_CHILD_TAGS => 1,
            SpecRule::CHILD_TAG_NAME_ONEOF => [
                'SCRIPT',
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4ADS,
        ],
        SpecRule::REQUIRES => [
            'amp-ad-exit configuration JSON',
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::AD_EXIT,
        ],
    ];
}
