<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\CssRuleset;

use AmpProject\Attribute;
use AmpProject\Format;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\CssRuleset;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * CSS ruleset class AmpNoTransformed.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $disabledBy
 * @property-read string $specUrl
 * @property-read int $maxBytes
 * @property-read int $maxBytesPerInlineStyle
 * @property-read bool $urlBytesIncluded
 * @property-read string $maxBytesSpecUrl
 * @property-read bool $allowAllDeclarationInStyle
 * @property-read array $imageUrlSpec
 * @property-read array $fontUrlSpec
 * @property-read bool $allowImportant
 * @property-read bool $expandVendorPrefixes
 */
final class AmpNoTransformed extends CssRuleset implements Identifiable
{
    /**
     * ID of the ruleset.
     *
     * @var string
     */
    const ID = 'AMP (no-transformed)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::DISABLED_BY => [
            Attribute::TRANSFORMED,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#stylesheets',
        SpecRule::MAX_BYTES => 75000,
        SpecRule::MAX_BYTES_PER_INLINE_STYLE => 1000,
        SpecRule::URL_BYTES_INCLUDED => true,
        SpecRule::MAX_BYTES_SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#maximum-size',
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
