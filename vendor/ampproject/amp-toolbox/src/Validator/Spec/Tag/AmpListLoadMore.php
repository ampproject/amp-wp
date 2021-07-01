<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpListLoadMore.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpListLoadMore extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-LIST-LOAD-MORE';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::LIST_LOAD_MORE,
        SpecRule::MANDATORY_PARENT => Extension::LIST_,
        SpecRule::ATTRS => [
            Attribute::LOAD_MORE_BUTTON => [
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::LOAD_MORE_BUTTON,
                    Attribute::LOAD_MORE_FAILED,
                    Attribute::LOAD_MORE_END,
                    Attribute::LOAD_MORE_LOADING,
                ],
            ],
            Attribute::LOAD_MORE_FAILED => [
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::LOAD_MORE_BUTTON,
                    Attribute::LOAD_MORE_FAILED,
                    Attribute::LOAD_MORE_END,
                    Attribute::LOAD_MORE_LOADING,
                ],
            ],
            Attribute::LOAD_MORE_LOADING => [
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::LOAD_MORE_BUTTON,
                    Attribute::LOAD_MORE_FAILED,
                    Attribute::LOAD_MORE_END,
                    Attribute::LOAD_MORE_LOADING,
                ],
            ],
            Attribute::LOAD_MORE_END => [
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::LOAD_MORE_BUTTON,
                    Attribute::LOAD_MORE_FAILED,
                    Attribute::LOAD_MORE_END,
                    Attribute::LOAD_MORE_LOADING,
                ],
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::LIST_,
        ],
    ];
}
