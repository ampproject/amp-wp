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
 * Attribute list class CommonLinkAttrs.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $charset
 * @property-read array $color
 * @property-read array $crossorigin
 * @property-read array $hreflang
 * @property-read array $media
 * @property-read array $sizes
 * @property-read array $target
 * @property-read array $type
 */
final class CommonLinkAttrs extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'common-link-attrs';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::CHARSET => [
            SpecRule::VALUE_CASEI => [
                'utf-8',
            ],
        ],
        Attribute::COLOR => [],
        Attribute::CROSSORIGIN => [],
        Attribute::HREFLANG => [],
        Attribute::MEDIA => [],
        Attribute::SIZES => [],
        Attribute::TARGET => [],
        Attribute::TYPE => [],
    ];
}
