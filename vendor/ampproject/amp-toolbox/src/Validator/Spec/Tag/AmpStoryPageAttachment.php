<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\DescendantTagList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryPageAttachment.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read string $descendantTagList
 * @property-read bool $mandatoryLastChild
 */
final class AmpStoryPageAttachment extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-story-page-attachment';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_PAGE_ATTACHMENT,
        SpecRule::SPEC_NAME => 'amp-story-page-attachment',
        SpecRule::ATTRS => [
            Attribute::CTA_TEXT => [],
            Attribute::TITLE => [],
            Attribute::CTA_IMAGE => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::CTA_IMAGE_2 => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::LAYOUT => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'nodisplay',
                ],
            ],
            Attribute::THEME => [
                SpecRule::VALUE => [
                    'dark',
                    'light',
                ],
            ],
        ],
        SpecRule::MANDATORY_ANCESTOR => Extension::STORY_PAGE,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::DESCENDANT_TAG_LIST => DescendantTagList\AmpStoryPageAttachmentAllowedDescendants::ID,
        SpecRule::MANDATORY_LAST_CHILD => true,
    ];
}
