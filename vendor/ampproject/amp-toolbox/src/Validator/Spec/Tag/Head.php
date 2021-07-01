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
 * Tag class Head.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read bool $mandatory
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 */
final class Head extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'HEAD';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::HEAD,
        SpecRule::MANDATORY => true,
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::HTML,
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#required-markup',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
            Format::AMP4EMAIL,
        ],
    ];
}
