<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpLiveListPagination.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array<bool>> $attrs
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class AmpLiveListPagination extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-LIVE-LIST [pagination]';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => '$REFERENCE_POINT',
        SpecRule::SPEC_NAME => 'AMP-LIVE-LIST [pagination]',
        SpecRule::ATTRS => [
            Attribute::PAGINATION => [
                SpecRule::MANDATORY => true,
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-live-list/#pagination',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'amp-live-list [pagination] child',
    ];
}
