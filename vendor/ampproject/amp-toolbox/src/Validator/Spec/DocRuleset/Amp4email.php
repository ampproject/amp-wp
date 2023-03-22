<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\DocRuleset;

use AmpProject\Format;
use AmpProject\Validator\Spec\DocRuleset;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Document ruleset class Amp4email.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<string> $htmlFormat
 * @property-read int $maxBytes
 * @property-read string $maxBytesSpecUrl
 */
final class Amp4email extends DocRuleset implements Identifiable
{
    /**
     * ID of the ruleset.
     *
     * @var string
     */
    const ID = 'AMP4EMAIL';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::HTML_FORMAT => [
            Format::AMP4EMAIL,
        ],
        SpecRule::MAX_BYTES => 200000,
        SpecRule::MAX_BYTES_SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/email-spec/amp-email-format/?format=email',
    ];
}
