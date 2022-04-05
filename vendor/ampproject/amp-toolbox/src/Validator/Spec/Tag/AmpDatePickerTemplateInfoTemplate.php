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
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpDatePickerTemplateInfoTemplate.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpDatePickerTemplateInfoTemplate extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-date-picker > template [info-template]';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::TEMPLATE,
        SpecRule::SPEC_NAME => 'amp-date-picker > template [info-template]',
        SpecRule::MANDATORY_PARENT => Extension::DATE_PICKER,
        SpecRule::ATTRS => [
            Attribute::INFO_TEMPLATE => [
                SpecRule::MANDATORY => true,
                SpecRule::DISPATCH_KEY => 'NAME_DISPATCH',
            ],
            Attribute::TYPE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'amp-mustache',
                ],
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::MUSTACHE,
        ],
    ];
}
