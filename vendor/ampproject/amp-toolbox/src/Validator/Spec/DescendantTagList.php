<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

use AmpProject\Exception\InvalidSpecRuleName;

/**
 * The base class for a single DescendantTagList that defines the set of allowed descendant tags.
 *
 * @package ampproject/amp-toolbox
 *
 * @property-read string $id ID of the descendant tag list.
 */
class DescendantTagList
{
    /**
     * ID of the descendant tag list.
     *
     * This needs to be overridden in the extending class.
     *
     * @var string
     */
    const ID = '[descendant tag list base class]';

    /**
     * Array of descendant tags.
     *
     * @var array<string>
     */
    const DESCENDANT_TAGS = [];

    /**
     * Get the ID of the descendant tag list.
     *
     * @return string ID of the descendant tag list.
     */
    public function getId()
    {
        return static::ID;
    }

    /**
     * Check whether a given descendant tag is contained within the list.
     *
     * @param string $descendantTag Descendant tag to check for.
     * @return bool Whether the given descendant tag is contained within the list.
     */
    public function has($descendantTag)
    {
        return in_array($descendantTag, static::DESCENDANT_TAGS, true);
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
            default:
                if (!array_key_exists($specRuleName, static::DESCENDANT_TAGS)) {
                    throw InvalidSpecRuleName::forSpecRuleName($specRuleName);
                }

                return static::DESCENDANT_TAGS[$specRuleName];
        }
    }
}
