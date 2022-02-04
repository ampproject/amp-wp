<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class SubscriptionsSectionContentSwgAmpCacheNonce.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $satisfies
 * @property-read array<string> $requires
 */
final class SubscriptionsSectionContentSwgAmpCacheNonce extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'subscriptions-section content swg_amp_cache_nonce';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SECTION,
        SpecRule::SPEC_NAME => 'subscriptions-section content swg_amp_cache_nonce',
        SpecRule::ATTRS => [
            Attribute::ENCRYPTED => [
                SpecRule::MANDATORY => true,
                SpecRule::DISPATCH_KEY => 'NAME_DISPATCH',
            ],
            Attribute::SUBSCRIPTIONS_SECTION => [
                SpecRule::VALUE_CASEI => [
                    'content',
                ],
            ],
            Attribute::SWG_AMP_CACHE_NONCE => [
                SpecRule::MANDATORY => true,
            ],
        ],
        SpecRule::MANDATORY_ANCESTOR => Element::BODY,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::SATISFIES => [
            'subscriptions-section content swg_amp_cache_nonce',
        ],
        SpecRule::REQUIRES => [
            'span swg_amp_cache_nonce',
        ],
    ];
}
