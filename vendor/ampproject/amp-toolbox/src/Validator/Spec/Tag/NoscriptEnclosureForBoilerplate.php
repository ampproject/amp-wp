<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Format;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class NoscriptEnclosureForBoilerplate.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $mandatory
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $disabledBy
 * @property-read string $descriptiveName
 */
final class NoscriptEnclosureForBoilerplate extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'noscript enclosure for boilerplate';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::NOSCRIPT,
        SpecRule::SPEC_NAME => 'noscript enclosure for boilerplate',
        SpecRule::MANDATORY => true,
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amp-boilerplate/?format=websites',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::DISABLED_BY => [
            Attribute::TRANSFORMED,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'noscript enclosure for boilerplate',
    ];
}
