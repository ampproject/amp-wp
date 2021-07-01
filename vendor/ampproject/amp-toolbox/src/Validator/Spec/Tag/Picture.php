<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class Picture.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $mandatoryParent
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 */
final class Picture extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'PICTURE';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::PICTURE,
        SpecRule::MANDATORY_PARENT => Element::NOSCRIPT,
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-img/',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
    ];
}
