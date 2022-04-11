<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

use AmpProject\Exception\InvalidDeclarationName;
use AmpProject\Exception\InvalidSpecRuleName;

/**
 * The base class for a single DeclarationList object that defines the set of allowed declarations for a specific type.
 *
 * @package ampproject/amp-toolbox
 *
 * @property-read string $id ID of the declaration list.
 */
abstract class DeclarationList
{
    /**
     * ID of the declaration list.
     *
     * This needs to be overridden in the extending class.
     *
     * @var string
     */
    const ID = '[declaration list base class]';

    /**
     * Array of declarations.
     *
     * @var array<array>
     */
    const DECLARATIONS = [];

    /**
     * Get the ID of the declaration list.
     *
     * @return string ID of the declaration list.
     */
    public function getId()
    {
        return static::ID;
    }

    /**
     * Check whether a given declaration is contained within the list.
     *
     * @param string $declaration Declaration to check for.
     * @return bool Whether the given declaration is contained within the list.
     */
    public function has($declaration)
    {
        return array_key_exists($declaration, static::DECLARATIONS);
    }

    /**
     * Get a specific declaration.
     *
     * @param string $declaration Declaration to get.
     * @return array Declaration data that was requested.
     */
    public function get($declaration)
    {
        if (!$this->has($declaration)) {
            throw InvalidDeclarationName::forDeclaration($declaration);
        }

        return static::DECLARATIONS[$declaration];
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
                if (!array_key_exists($specRuleName, static::DECLARATIONS)) {
                    throw InvalidSpecRuleName::forSpecRuleName($specRuleName);
                }

                return static::DECLARATIONS[$specRuleName];
        }
    }
}
