<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;
use AmpProject\Validator\Spec\TagWithExtensionSpec;

/**
 * Tag class ScriptCustomTemplateAmpMustacheAmp4email.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<string> $attrLists
 * @property-read array<string> $htmlFormat
 * @property-read string $extensionSpec
 */
final class ScriptCustomTemplateAmpMustacheAmp4email extends TagWithExtensionSpec implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'SCRIPT[custom-template=amp-mustache] (AMP4EMAIL)';

    /**
     * Array of extension spec rules.
     *
     * @var array
     */
    const EXTENSION_SPEC = [
        SpecRule::NAME => 'amp-mustache',
        SpecRule::VERSION => [
            '0.1',
            '0.2',
        ],
        SpecRule::DEPRECATED_VERSION => [
            '0.1',
        ],
        SpecRule::EXTENSION_TYPE => 'CUSTOM_TEMPLATE',
    ];

    /**
     * Latest version of the extension.
     *
     * @var string
     */
    const LATEST_VERSION = '0.2';

    /**
     * Meta data about the specific versions.
     *
     * @var array
     */
    const VERSIONS_META = [
        '0.1' => [
            'hasCss' => false,
            'hasBento' => false,
        ],
        '0.2' => [
            'hasCss' => false,
            'hasBento' => false,
        ],
    ];

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SCRIPT,
        SpecRule::SPEC_NAME => 'SCRIPT[custom-template=amp-mustache] (AMP4EMAIL)',
        SpecRule::ATTR_LISTS => [
            AttributeList\CommonExtensionAttrs::ID,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4EMAIL,
        ],
        SpecRule::EXTENSION_SPEC => self::EXTENSION_SPEC,
    ];
}
