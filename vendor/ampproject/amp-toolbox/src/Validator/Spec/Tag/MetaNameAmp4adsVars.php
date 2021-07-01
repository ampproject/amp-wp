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
 * Tag class MetaNameAmp4adsVars.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array<string> $htmlFormat
 */
final class MetaNameAmp4adsVars extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'meta name=amp4ads-vars-*';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::META,
        SpecRule::SPEC_NAME => 'meta name=amp4ads-vars-*',
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::CONTENT => [
                SpecRule::MANDATORY => true,
            ],
            Attribute::NAME => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX => 'amp4ads-vars-.+',
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4ADS,
        ],
    ];
}
