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
 * Tag class SubscriptionsScriptCiphertext.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<array<array<string>>> $cdata
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class SubscriptionsScriptCiphertext extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'subscriptions script ciphertext';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SCRIPT,
        SpecRule::SPEC_NAME => 'subscriptions script ciphertext',
        SpecRule::MANDATORY_PARENT => 'subscriptions-section content swg_amp_cache_nonce',
        SpecRule::ATTRS => [
            Attribute::CIPHERTEXT => [
                SpecRule::MANDATORY => true,
                SpecRule::DISPATCH_KEY => 'NAME_DISPATCH',
            ],
            Attribute::TYPE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_CASEI => [
                    'application/octet-stream',
                ],
            ],
        ],
        SpecRule::CDATA => [
            SpecRule::DISALLOWED_CDATA_REGEX => [
                [
                    SpecRule::REGEX => '<!--',
                    SpecRule::ERROR_MESSAGE => 'html comments',
                ],
            ],
        ],
        SpecRule::MANDATORY_ANCESTOR => Element::BODY,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
    ];
}
