<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class ScriptAmpOnerrorV0JsOrV0Mjs.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $cdata
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $enabledBy
 * @property-read string $descriptiveName
 */
final class ScriptAmpOnerrorV0JsOrV0Mjs extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'script amp-onerror (v0.js or v0.mjs)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SCRIPT,
        SpecRule::SPEC_NAME => 'script amp-onerror (v0.js or v0.mjs)',
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::AMP_ONERROR => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
            ],
        ],
        SpecRule::CDATA => [
            SpecRule::CDATA_REGEX => '\[]\.slice\.call\(document\.querySelectorAll\("script\[src\*=\'\/v0\.js\'],script\[src\*=\'\/v0\.mjs\']"\)\)\.forEach\(function\(s\){s\.onerror=function\(\){document\.querySelector\(\'style\[amp-boilerplate]\'\)\.textContent=\'\'}}\)|document\.querySelector\("script\[src\*=\'\/v0\.js\']"\)\.onerror=function\(\){document\.querySelector\(\'style\[amp-boilerplate]\'\)\.textContent=\'\'}',
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::ENABLED_BY => [
            Attribute::TRANSFORMED,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'script amp-onerror',
    ];
}
