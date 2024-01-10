<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Validator\Spec\DescendantTagList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryCtaLayer.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $mandatoryAncestor
 * @property-read array<array<string>> $referencePoints
 * @property-read array<string> $htmlFormat
 * @property-read string $descendantTagList
 * @property-read bool $mandatoryLastChild
 */
final class AmpStoryCtaLayer extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-CTA-LAYER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_CTA_LAYER,
        SpecRule::MANDATORY_ANCESTOR => Extension::STORY_PAGE,
        SpecRule::REFERENCE_POINTS => [
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-STORY-CTA-LAYER animate-in',
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::DESCENDANT_TAG_LIST => DescendantTagList\AmpStoryCtaLayerAllowedDescendants::ID,
        SpecRule::MANDATORY_LAST_CHILD => true,
    ];
}
