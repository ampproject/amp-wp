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
use AmpProject\Validator\Spec\ExtensionSpec;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;
use AmpProject\Validator\Spec\TagWithExtensionSpec;

/**
 * Tag class ScriptAmpMraid.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read array<string> $htmlFormat
 * @property-read string $extensionSpec
 */
final class ScriptAmpMraid extends Tag implements Identifiable, TagWithExtensionSpec
{
    use ExtensionSpec;

    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'SCRIPT [amp-mraid]';

    /**
     * Array of extension spec rules.
     *
     * @var array
     */
    const EXTENSION_SPEC = [
        SpecRule::NAME => 'amp-mraid',
        SpecRule::VERSION => [
            '0.1',
            'latest',
        ],
        SpecRule::REQUIRES_USAGE => 'NONE',
        SpecRule::EXTENSION_TYPE => 'HOST_SERVICE',
    ];

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SCRIPT,
        SpecRule::ATTRS => [
            Attribute::NO_FALLBACK => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\CommonExtensionAttrs::ID,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4ADS,
        ],
        SpecRule::EXTENSION_SPEC => self::EXTENSION_SPEC,
    ];
}
