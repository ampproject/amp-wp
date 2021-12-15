<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Layout;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpOrientationObserver.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<array<string>> $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpOrientationObserver extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-ORIENTATION-OBSERVER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::ORIENTATION_OBSERVER,
        SpecRule::ATTRS => [
            Attribute::ALPHA_RANGE => [
                SpecRule::VALUE_REGEX => '(\d+)\s{1}(\d+)',
            ],
            Attribute::BETA_RANGE => [
                SpecRule::VALUE_REGEX => '(\d+)\s{1}(\d+)',
            ],
            Attribute::GAMMA_RANGE => [
                SpecRule::VALUE_REGEX => '(\d+)\s{1}(\d+)',
            ],
            Attribute::SMOOTHING => [
                SpecRule::VALUE_REGEX => '[0-9]+|',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::NODISPLAY,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::ORIENTATION_OBSERVER,
        ],
    ];
}
