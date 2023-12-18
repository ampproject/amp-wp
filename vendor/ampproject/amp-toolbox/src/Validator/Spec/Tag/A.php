<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class A.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 */
final class A extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'A';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::A,
        SpecRule::ATTRS => [
            Attribute::BORDER => [],
            Attribute::DOWNLOAD => [],
            Attribute::HREF => [
                SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::FTP,
                        Protocol::GEO,
                        Protocol::HTTP,
                        Protocol::HTTPS,
                        Protocol::MAILTO,
                        Protocol::MAPS,
                        Protocol::BIP,
                        Protocol::BBMI,
                        Protocol::CHROME,
                        Protocol::ITMS_SERVICES,
                        Protocol::FACETIME,
                        Protocol::FB_ME,
                        Protocol::FB_MESSENGER,
                        Protocol::FEED,
                        Protocol::INTENT,
                        Protocol::LINE,
                        Protocol::MICROSOFT_EDGE,
                        Protocol::SKYPE,
                        Protocol::SMS,
                        Protocol::SNAPCHAT,
                        Protocol::TEL,
                        Protocol::TG,
                        Protocol::THREEMA,
                        Protocol::TWITTER,
                        Protocol::VIBER,
                        Protocol::WEBCAL,
                        Protocol::WEB_MASTODON,
                        Protocol::WH,
                        Protocol::WHATSAPP,
                    ],
                    SpecRule::ALLOW_EMPTY => true,
                ],
            ],
            Attribute::HREFLANG => [],
            Attribute::MEDIA => [],
            Attribute::REFERRERPOLICY => [],
            Attribute::REL => [
                SpecRule::DISALLOWED_VALUE_REGEX => '(^|\s)(components|dns-prefetch|import|manifest|preconnect|prefetch|preload|prerender|serviceworker|stylesheet|subresource)(\s|$)',
            ],
            Attribute::ROLE => [
                SpecRule::IMPLICIT => true,
            ],
            Attribute::SHOW_TOOLTIP => [
                SpecRule::VALUE => [
                    'auto',
                    'true',
                ],
            ],
            Attribute::TABINDEX => [
                SpecRule::IMPLICIT => true,
            ],
            Attribute::TARGET => [
                SpecRule::VALUE => [
                    '_blank',
                    '_self',
                    '_top',
                ],
            ],
            Attribute::TYPE => [
                SpecRule::VALUE_CASEI => [
                    'text/html',
                    'application/rss+xml',
                ],
            ],
            '[href]' => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ClickAttributions::ID,
            AttributeList\NameAttr::ID,
            AttributeList\PrivateClickMeasurementAttributes::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#links',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
        ],
    ];
}
