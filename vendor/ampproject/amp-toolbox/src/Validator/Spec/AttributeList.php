<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

use AmpProject\Exception\InvalidAttributeName;
use AmpProject\Exception\InvalidSpecRuleName;

/**
 * The base class for a single AttributeList object that defines a possible set of allowed attributes.
 *
 * @package ampproject/amp-toolbox
 *
 * @property-read string $id ID of the attribute list.
 */
class AttributeList
{
    /**
     * ID of the attribute list.
     *
     * This needs to be overridden in the extending class.
     *
     * @var string
     */
    const ID = '[attribute list base class]';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [];

    /**
     * Get the ID of the attribute list.
     *
     * @return string ID of the attribute list.
     */
    public function getId()
    {
        return static::ID;
    }

    /**
     * Check whether a given attribute is contained within the list.
     *
     * @param string $attribute Attribute to check for.
     * @return bool Whether the given attribute is contained within the list.
     */
    public function has($attribute)
    {
        return array_key_exists($attribute, static::ATTRIBUTES);
    }

    /**
     * Get a specific attribute.
     *
     * @param string $attribute Attribute to get.
     * @return array Attribute data that was requested.
     */
    public function get($attribute)
    {
        if (!$this->has($attribute)) {
            throw InvalidAttributeName::forAttribute($attribute);
        }

        return static::ATTRIBUTES[$attribute];
    }

    /**
     * Magic getter to return the attributes.
     *
     * @param string $attribute Name of the attribute to return.
     * @return mixed Value of the spec rule.
     */
    public function __get($attribute)
    {
        $attribute = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $attribute));

        if (substr($attribute, -8) === '_binding') {
            $attribute = '[' . substr($attribute, 0, -8) . ']';
        }

        switch ($attribute) {
            case 'id':
                return static::ID;
            default:
                if (!array_key_exists($attribute, static::ATTRIBUTES)) {
                    throw InvalidSpecRuleName::forSpecRuleName($attribute);
                }

                return static::ATTRIBUTES[$attribute];
        }
    }
}
