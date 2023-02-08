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
 * Tag class AmpListDivFetchError.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class AmpListDivFetchError extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-LIST DIV [fetch-error]';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::DIV,
        SpecRule::SPEC_NAME => 'AMP-LIST DIV [fetch-error]',
        SpecRule::ATTRS => [
            Attribute::ALIGN => [],
            Attribute::FETCH_ERROR => [
                SpecRule::MANDATORY => true,
            ],
        ],
        SpecRule::MANDATORY_ANCESTOR => Extension::LIST_,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
            Format::AMP4EMAIL,
        ],
    ];
}
