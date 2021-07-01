<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\CssRuleset;

use AmpProject\Format;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\CssRuleset;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * CSS ruleset class Amp4ads.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<string> $htmlFormat
 * @property-read string $specUrl
 * @property-read string $maxBytesSpecUrl
 * @property-read bool $allowAllDeclarationInStyle
 * @property-read array $imageUrlSpec
 * @property-read array $fontUrlSpec
 * @property-read bool $allowImportant
 * @property-read bool $expandVendorPrefixes
 */
final class Amp4ads extends CssRuleset implements Identifiable
{
    /**
     * ID of the ruleset.
     *
     * @var string
     */
    const ID = 'AMP4ADS';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::HTML_FORMAT => [
            Format::AMP4ADS,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/a4a_spec/#css',
        SpecRule::MAX_BYTES_SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/a4a_spec/#css',
        SpecRule::ALLOW_ALL_DECLARATION_IN_STYLE => true,
        SpecRule::IMAGE_URL_SPEC => [
            SpecRule::PROTOCOL => [
                Protocol::HTTPS,
                Protocol::HTTP,
                Protocol::DATA,
            ],
            SpecRule::ALLOW_EMPTY => true,
        ],
        SpecRule::FONT_URL_SPEC => [
            SpecRule::PROTOCOL => [
                Protocol::HTTPS,
                Protocol::HTTP,
                Protocol::DATA,
            ],
            SpecRule::ALLOW_EMPTY => true,
        ],
        SpecRule::ALLOW_IMPORTANT => false,
        SpecRule::EXPAND_VENDOR_PREFIXES => true,
    ];
}
