<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class NonceAttr.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $nonce
 */
final class NonceAttr extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'nonce-attr';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::NONCE => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
    ];
}
