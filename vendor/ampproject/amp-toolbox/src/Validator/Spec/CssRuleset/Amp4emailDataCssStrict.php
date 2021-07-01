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
use AmpProject\Validator\Spec\DeclarationList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * CSS ruleset class Amp4emailDataCssStrict.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $enabledBy
 * @property-read string $specUrl
 * @property-read int $maxBytes
 * @property-read int $maxBytesPerInlineStyle
 * @property-read bool $urlBytesIncluded
 * @property-read string $maxBytesSpecUrl
 * @property-read bool $allowAllDeclarationInStyle
 * @property-read array<string> $declarationList
 * @property-read array<array<string>> $imageUrlSpec
 * @property-read bool $allowImportant
 * @property-read bool $maxBytesIsWarning
 * @property-read bool $expandVendorPrefixes
 */
final class Amp4emailDataCssStrict extends CssRuleset implements Identifiable
{
    /**
     * ID of the ruleset.
     *
     * @var string
     */
    const ID = 'AMP4EMAIL (data-css-strict)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::HTML_FORMAT => [
            Format::AMP4EMAIL,
        ],
        SpecRule::ENABLED_BY => [
            Attribute::DATA_CSS_STRICT,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/email-spec/amp-email-css',
        SpecRule::MAX_BYTES => 75000,
        SpecRule::MAX_BYTES_PER_INLINE_STYLE => 1000,
        SpecRule::URL_BYTES_INCLUDED => true,
        SpecRule::MAX_BYTES_SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#maximum-size',
        SpecRule::ALLOW_ALL_DECLARATION_IN_STYLE => false,
        SpecRule::DECLARATION_LIST => [
            DeclarationList\EmailSpecificDeclarations::ID,
        ],
        SpecRule::IMAGE_URL_SPEC => [
            SpecRule::PROTOCOL => [
                Protocol::HTTPS,
            ],
        ],
        SpecRule::ALLOW_IMPORTANT => false,
        SpecRule::MAX_BYTES_IS_WARNING => false,
        SpecRule::EXPAND_VENDOR_PREFIXES => false,
    ];
}
