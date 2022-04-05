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
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpSocialShare.
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
final class AmpSocialShare extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-SOCIAL-SHARE';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::SOCIAL_SHARE,
        SpecRule::ATTRS => [
            Attribute::DATA_SHARE_ENDPOINT => [
                SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::FTP,
                        Protocol::HTTP,
                        Protocol::HTTPS,
                        Protocol::MAILTO,
                        Protocol::BBMI,
                        Protocol::FB_ME,
                        Protocol::FB_MESSENGER,
                        Protocol::INTENT,
                        Protocol::LINE,
                        Protocol::SKYPE,
                        Protocol::SMS,
                        Protocol::SNAPCHAT,
                        Protocol::TEL,
                        Protocol::TG,
                        Protocol::THREEMA,
                        Protocol::VIBER,
                        Protocol::WH,
                        Protocol::WHATSAPP,
                    ],
                    SpecRule::ALLOW_RELATIVE => false,
                ],
            ],
            Attribute::TYPE => [
                SpecRule::MANDATORY => true,
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::CONTAINER,
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
            Format::AMP4ADS,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::SOCIAL_SHARE,
        ],
    ];
}
