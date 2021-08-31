<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

use AmpProject\Exception\InvalidSpecRuleName;

/**
 * The base class for a single Tag spec definition that provides the validation rules for a specific HTML element.
 *
 * @package ampproject/amp-toolbox
 *
 * @property-read string        $id                     ID of the tag.
 * @property-read array<string> $alsoRequiresTagWarning
 * @property-read array         $ampLayout
 * @property-read array<string> $attrLists
 * @property-read array         $attrs
 * @property-read array         $cdata
 * @property-read array         $childTags
 * @property-read string        $deprecation
 * @property-read string        $deprecationUrl
 * @property-read string        $descendantTagList
 * @property-read string        $descriptiveName
 * @property-read array<string> $disabledBy
 * @property-read array<string> $disallowedAncestor
 * @property-read array<string> $enabledBy
 * @property-read array<string> $excludes
 * @property-read bool          $explicitAttrsOnly
 * @property-read array         $extensionSpec
 * @property-read array<string> $htmlFormat
 * @property-read bool          $mandatory
 * @property-read string        $mandatoryAlternatives
 * @property-read string        $mandatoryAncestor
 * @property-read string        $mandatoryAncestorSuggestedAlternative
 * @property-read bool          $mandatoryLastChild
 * @property-read string        $mandatoryParent
 * @property-read array         $markDescendants
 * @property-read string        $namedId
 * @property-read array<array>  $referencePoints
 * @property-read array<string> $requires
 * @property-read array<string> $requiresExtension
 * @property-read array<string> $satisfies
 * @property-read bool          $siblingsDisallowed
 * @property-read string        $specName
 * @property-read string        $specUrl
 * @property-read string        $tagName
 * @property-read bool          $unique
 * @property-read bool          $uniqueWarning
 */
class Tag
{
    /**
     * ID of the tag.
     *
     * This needs to be overridden in the extending class.
     *
     * @var string
     */
    const ID = '[tag base class]';

    /**
     * Spec data of the tag.
     *
     * @var array
     */
    const SPEC = [];

    /**
     * Get the ID of the tag.
     *
     * @return string ID of the tag.
     */
    public function getId()
    {
        return static::ID;
    }

    /**
     * Check whether a given spec rule is present.
     *
     * @param string $specRuleName Name of the spec rule to check for.
     * @return bool Whether the given spec rule is contained in the spec.
     */
    public function has($specRuleName)
    {
        return array_key_exists($specRuleName, static::SPEC);
    }

    /**
     * Get a specific spec rule.
     *
     * @param string $specRuleName Name of the spec rule to get.
     * @return array Spec rule data that was requested.
     */
    public function get($specRuleName)
    {
        if (!$this->has($specRuleName)) {
            throw InvalidSpecRuleName::forSpecRuleName($specRuleName);
        }

        return static::SPEC[$specRuleName];
    }

    /**
     * Magic getter to return the spec rules.
     *
     * @param string $specRuleName Name of the spec rule to return.
     * @return mixed Value of the spec rule.
     */
    public function __get($specRuleName)
    {
        switch ($specRuleName) {
            case 'id':
                return static::ID;
            case SpecRule::EXPLICIT_ATTRS_ONLY:
            case SpecRule::MANDATORY:
            case SpecRule::MANDATORY_LAST_CHILD:
            case SpecRule::SIBLINGS_DISALLOWED:
            case SpecRule::UNIQUE:
            case SpecRule::UNIQUE_WARNING:
                return array_key_exists($specRuleName, static::SPEC) ? static::SPEC[$specRuleName] : false;
            case SpecRule::ALSO_REQUIRES_TAG_WARNING:
            case SpecRule::ATTR_LISTS:
            case SpecRule::DISABLED_BY:
            case SpecRule::DISALLOWED_ANCESTOR:
            case SpecRule::ENABLED_BY:
            case SpecRule::EXCLUDES:
            case SpecRule::HTML_FORMAT:
            case SpecRule::REQUIRES:
            case SpecRule::REQUIRES_EXTENSION:
            case SpecRule::SATISFIES:
                return array_key_exists($specRuleName, static::SPEC) ? static::SPEC[$specRuleName] : [];
            default:
                if (!array_key_exists($specRuleName, static::SPEC)) {
                    throw InvalidSpecRuleName::forSpecRuleName($specRuleName);
                }

                return static::SPEC[$specRuleName];
        }
    }
}
