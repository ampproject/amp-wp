<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Format;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class LinkRelStylesheetForFonts.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 * @property-read string $namedId
 * @property-read string $descriptiveName
 */
final class LinkRelStylesheetForFonts extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'link rel=stylesheet for fonts';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::LINK,
        SpecRule::SPEC_NAME => 'link rel=stylesheet for fonts',
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::ASYNC => [],
            Attribute::CROSSORIGIN => [],
            Attribute::HREF => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX => 'https://cdn\.materialdesignicons\.com/([0-9]+\.?)+/css/materialdesignicons\.min\.css|https://cloud\.typography\.com/[0-9]*/[0-9]*/css/fonts\.css|https://fast\.fonts\.net/.*|https://fonts\.googleapis\.com/css2?\?.*|https://fonts\.googleapis\.com/icon\?.*|https://fonts\.googleapis\.com/earlyaccess/.*\.css|https://maxcdn\.bootstrapcdn\.com/font-awesome/([0-9]+\.?)+/css/font-awesome\.min\.css(\?.*)?|https://(use|pro|kit)\.fontawesome\.com/releases/v([0-9]+\.?)+/css/[0-9a-zA-Z-]+\.css|https://(use|pro|kit)\.fontawesome\.com/[0-9a-zA-Z-]+\.css|https://use\.typekit\.net/[\w\p{L}\p{N}_]+\.css',
            ],
            Attribute::INTEGRITY => [],
            Attribute::MEDIA => [],
            Attribute::REL => [
                SpecRule::MANDATORY => true,
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
                SpecRule::VALUE_CASEI => [
                    'stylesheet',
                ],
            ],
            Attribute::TYPE => [
                SpecRule::VALUE_CASEI => [
                    'text/css',
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NonceAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#custom-fonts',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
        ],
        SpecRule::NAMED_ID => 'LINK_FONT_STYLESHEET',
        SpecRule::DESCRIPTIVE_NAME => 'link rel=stylesheet for fonts',
    ];
}
