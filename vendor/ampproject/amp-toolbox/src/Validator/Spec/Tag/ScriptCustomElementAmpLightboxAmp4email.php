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
 * Tag class ScriptCustomElementAmpLightboxAmp4email.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<string> $attrLists
 * @property-read string $deprecation
 * @property-read string $deprecationUrl
 * @property-read array<string> $htmlFormat
 * @property-read string $extensionSpec
 */
final class ScriptCustomElementAmpLightboxAmp4email extends Tag implements Identifiable, TagWithExtensionSpec
{
    use ExtensionSpec;

    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'SCRIPT[custom-element=amp-lightbox] (AMP4EMAIL)';

    /**
     * Array of extension spec rules.
     *
     * @var array
     */
    const EXTENSION_SPEC = [
        SpecRule::NAME => 'amp-lightbox',
        SpecRule::VERSION => [
            '0.1',
            'latest',
        ],
        SpecRule::VERSION_NAME => 'v0.1',
    ];

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SCRIPT,
        SpecRule::SPEC_NAME => 'SCRIPT[custom-element=amp-lightbox] (AMP4EMAIL)',
        SpecRule::ATTR_LISTS => [
            AttributeList\CommonExtensionAttrs::ID,
        ],
        SpecRule::DEPRECATION => 'amp-lightbox cannot be properly positioned in emails and will soon be invalid in AMP4EMAIL.',
        SpecRule::DEPRECATION_URL => 'https://github.com/ampproject/amphtml/issues/23170',
        SpecRule::HTML_FORMAT => [
            Format::AMP4EMAIL,
        ],
        SpecRule::EXTENSION_SPEC => self::EXTENSION_SPEC,
    ];
}
