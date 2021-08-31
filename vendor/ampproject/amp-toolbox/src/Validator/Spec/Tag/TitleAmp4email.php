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
 * Tag class TitleAmp4email.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array> $attrs
 * @property-read string $deprecation
 * @property-read string $deprecationUrl
 * @property-read array<string> $htmlFormat
 */
final class TitleAmp4email extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'title [AMP4EMAIL]';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::TITLE,
        SpecRule::SPEC_NAME => 'title [AMP4EMAIL]',
        SpecRule::ATTRS => [
            '[text]' => [],
        ],
        SpecRule::DEPRECATION => 'Title tags in email have no meaning. This tag may become invalid in the future.',
        SpecRule::DEPRECATION_URL => 'https://github.com/ampproject/amphtml/issues/22318',
        SpecRule::HTML_FORMAT => [
            Format::AMP4EMAIL,
        ],
    ];
}
