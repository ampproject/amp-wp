<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class NoscriptEnclosureForAmpStyleTags.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read string $specUrl
 * @property-read array $childTags
 * @property-read array<string> $htmlFormat
 */
final class NoscriptEnclosureForAmpStyleTags extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'noscript enclosure for amp style tags';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::NOSCRIPT,
        SpecRule::SPEC_NAME => 'noscript enclosure for amp style tags',
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amp-boilerplate/?format=websites',
        SpecRule::CHILD_TAGS => [
            SpecRule::CHILD_TAG_NAME_ONEOF => [
                'STYLE',
            ],
            SpecRule::MANDATORY_MIN_NUM_CHILD_TAGS => 1,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
    ];
}
