<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\ExtensionSpec;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;
use AmpProject\Validator\Spec\TagWithExtensionSpec;

/**
 * Tag class ScriptCustomElementAmpFitTextAmp4email.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<string> $attrLists
 * @property-read array<string> $htmlFormat
 * @property-read string $extensionSpec
 */
final class ScriptCustomElementAmpFitTextAmp4email extends Tag implements Identifiable, TagWithExtensionSpec
{
    use ExtensionSpec;

    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'SCRIPT[custom-element=amp-fit-text] (AMP4EMAIL)';

    /**
     * Array of extension spec rules.
     *
     * @var array
     */
    const EXTENSION_SPEC = [
        SpecRule::NAME => 'amp-fit-text',
        SpecRule::VERSION => [
            '0.1',
        ],
        SpecRule::DEPRECATED_ALLOW_DUPLICATES => true,
        SpecRule::REQUIRES_USAGE => 'EXEMPTED',
        SpecRule::VERSION_NAME => 'v0.1',
    ];

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SCRIPT,
        SpecRule::SPEC_NAME => 'SCRIPT[custom-element=amp-fit-text] (AMP4EMAIL)',
        SpecRule::ATTR_LISTS => [
            AttributeList\CommonExtensionAttrs::ID,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4EMAIL,
        ],
        SpecRule::EXTENSION_SPEC => self::EXTENSION_SPEC,
    ];
}
