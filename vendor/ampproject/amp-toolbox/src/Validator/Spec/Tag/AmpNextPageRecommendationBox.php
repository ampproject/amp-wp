<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpNextPageRecommendationBox.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array<bool>> $attrs
 * @property-read array<string> $htmlFormat
 */
final class AmpNextPageRecommendationBox extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-NEXT-PAGE > [recommendation-box]';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => '$REFERENCE_POINT',
        SpecRule::SPEC_NAME => 'AMP-NEXT-PAGE > [recommendation-box]',
        SpecRule::MANDATORY_PARENT => Extension::NEXT_PAGE,
        SpecRule::ATTRS => [
            Attribute::RECOMMENDATION_BOX => [
                SpecRule::MANDATORY => true,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
    ];
}
