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
use AmpProject\Protocol;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class Audio.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<array> $attrs
 * @property-read string $specUrl
 * @property-read string $mandatoryAncestor
 * @property-read string $mandatoryAncestorSuggestedAlternative
 * @property-read array<string> $htmlFormat
 */
final class Audio extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AUDIO';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::AUDIO,
        SpecRule::ATTRS => [
            Attribute::AUTOPLAY => [],
            Attribute::CONTROLS => [],
            Attribute::LOOP => [],
            Attribute::MUTED => [],
            Attribute::PRELOAD => [],
            Attribute::SRC => [
                SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::DATA,
                        Protocol::HTTPS,
                    ],
                    SpecRule::ALLOW_RELATIVE => false,
                ],
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-audio/',
        SpecRule::MANDATORY_ANCESTOR => Element::NOSCRIPT,
        SpecRule::MANDATORY_ANCESTOR_SUGGESTED_ALTERNATIVE => Extension::AUDIO,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
    ];
}
