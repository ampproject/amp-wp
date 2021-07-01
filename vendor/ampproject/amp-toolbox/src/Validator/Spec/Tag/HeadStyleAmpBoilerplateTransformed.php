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
 * Tag class HeadStyleAmpBoilerplateTransformed.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array $cdata
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $enabledBy
 * @property-read string $descriptiveName
 */
final class HeadStyleAmpBoilerplateTransformed extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'head > style[amp-boilerplate] (transformed)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::STYLE,
        SpecRule::SPEC_NAME => 'head > style[amp-boilerplate] (transformed)',
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::AMP_BOILERPLATE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_PARENT_DISPATCH',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NonceAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amp-boilerplate/?format=websites',
        SpecRule::CDATA => [
            SpecRule::CDATA_REGEX => '\s*body\s*{\s*-webkit-animation:\s*-amp-start\s+8s\s+steps\(1,\s*end\)\s+0s\s+1\s+normal\s+both;\s*-moz-animation:\s*-amp-start\s+8s\s+steps\s*\(1\s*,\s*end\s*\)\s+0s\s+1\s+normal\s+both;\s*-ms-animation:\s*-amp-start\s+8s\s+steps\s*\(1\s*,\s*end\s*\)\s+0s\s+1\s+normal\s+both;\s*animation:\s*-amp-start\s+8s\s+steps\(1,\s*end\)\s+0s\s+1\s+normal\s+both;?\s*}\s*@-webkit-keyframes\s+-amp-start\s*{\s*from\s*{\s*visibility:\s*hidden;?\s*}\s*to\s*{\s*visibility:\s*visible;?\s*}\s*}\s*@-moz-keyframes\s+-amp-start\s*{\s*from\s*{\s*visibility:\s*hidden;?\s*}\s*to\s*{\s*visibility:\s*visible;?\s*}\s*}\s*@-ms-keyframes\s+-amp-start\s*{\s*from\s*{\s*visibility:\s*hidden;?\s*}\s*to\s*{\s*visibility:\s*visible;?\s*}\s*}\s*@-o-keyframes\s+-amp-start\s*{\s*from\s*{\s*visibility:\s*hidden;?\s*}\s*to\s*{\s*visibility:\s*visible;?\s*}\s*}\s*@keyframes\s+-amp-start\s*{\s*from\s*{\s*visibility:\s*hidden;?\s*}\s*to\s*{\s*visibility:\s*visible;?\s*}\s*}\s*',
            SpecRule::DOC_CSS_BYTES => false,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::ENABLED_BY => [
            Attribute::TRANSFORMED,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'head > style[amp-boilerplate]',
    ];
}
