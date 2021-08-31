<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Layout;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpDateCountdown.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpDateCountdown extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-DATE-COUNTDOWN';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::DATE_COUNTDOWN,
        SpecRule::ATTRS => [
            Attribute::BIGGEST_UNIT => [
                SpecRule::VALUE_CASEI => [
                    'days',
                    'hours',
                    'minutes',
                    'seconds',
                ],
            ],
            Attribute::COUNT_UP => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::DATA_COUNT_UP => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::END_DATE => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::END_DATE,
                    Attribute::TIMELEFT_MS,
                    Attribute::TIMESTAMP_MS,
                    Attribute::TIMESTAMP_SECONDS,
                ],
                SpecRule::VALUE_REGEX => '\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d(:[0-5]\d(\.\d+)?)?(Z|[+-][0-1][0-9]:[0-5][0-9])',
            ],
            Attribute::LOCALE => [
                SpecRule::VALUE_CASEI => [
                    'de',
                    'en',
                    'es',
                    'fr',
                    'id',
                    'it',
                    'ja',
                    'ko',
                    'nl',
                    'pt',
                    'ru',
                    'th',
                    'tr',
                    'vi',
                    'zh-cn',
                    'zh-tw',
                ],
            ],
            Attribute::OFFSET_SECONDS => [
                SpecRule::VALUE_REGEX => '-?\d+',
            ],
            Attribute::TEMPLATE => [
                SpecRule::VALUE_ONEOF_SET => 'TEMPLATE_IDS',
            ],
            Attribute::TIMELEFT_MS => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::END_DATE,
                    Attribute::TIMELEFT_MS,
                    Attribute::TIMESTAMP_MS,
                    Attribute::TIMESTAMP_SECONDS,
                ],
                SpecRule::VALUE_REGEX => '\d+',
            ],
            Attribute::TIMESTAMP_MS => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::END_DATE,
                    Attribute::TIMELEFT_MS,
                    Attribute::TIMESTAMP_MS,
                    Attribute::TIMESTAMP_SECONDS,
                ],
                SpecRule::VALUE_REGEX => '\d{13}',
            ],
            Attribute::TIMESTAMP_SECONDS => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::END_DATE,
                    Attribute::TIMELEFT_MS,
                    Attribute::TIMESTAMP_MS,
                    Attribute::TIMESTAMP_SECONDS,
                ],
                SpecRule::VALUE_REGEX => '\d{10}',
            ],
            Attribute::WHEN_ENDED => [
                SpecRule::VALUE_CASEI => [
                    'continue',
                    'stop',
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::FILL,
                Layout::FIXED,
                Layout::FIXED_HEIGHT,
                Layout::FLEX_ITEM,
                Layout::NODISPLAY,
                Layout::RESPONSIVE,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::DATE_COUNTDOWN,
        ],
    ];
}
