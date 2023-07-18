<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Html\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class ExtendedAmpGlobal.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $iAmphtmlLayout
 * @property-read array $media
 * @property-read array<array<string>> $noloading
 */
final class ExtendedAmpGlobal extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'extended-amp-global';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::I_AMPHTML_LAYOUT => [
            SpecRule::VALUE_CASEI => [
                'container',
                'fill',
                'fixed',
                'fixed-height',
                'flex-item',
                'fluid',
                'intrinsic',
                'nodisplay',
                'responsive',
            ],
            SpecRule::ENABLED_BY => [
                Attribute::TRANSFORMED,
            ],
        ],
        Attribute::MEDIA => [],
        Attribute::NOLOADING => [
            SpecRule::VALUE => [
                '',
            ],
        ],
    ];
}
