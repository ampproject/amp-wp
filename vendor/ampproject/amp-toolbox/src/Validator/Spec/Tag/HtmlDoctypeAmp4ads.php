<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class HtmlDoctypeAmp4ads.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $mandatory
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class HtmlDoctypeAmp4ads extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'html doctype (AMP4ADS)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::_DOCTYPE,
        SpecRule::SPEC_NAME => 'html doctype (AMP4ADS)',
        SpecRule::MANDATORY => true,
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => '$ROOT',
        SpecRule::ATTRS => [
            Attribute::HTML => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#required-markup',
        SpecRule::HTML_FORMAT => [
            Format::AMP4ADS,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'html !doctype',
    ];
}
