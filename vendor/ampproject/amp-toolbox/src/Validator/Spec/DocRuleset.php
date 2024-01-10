<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

use AmpProject\Exception\InvalidSpecRuleName;

/**
 * The base class for a single DocRuleset object that defines the validation rules that apply to the entire document.
 *
 * @package ampproject/amp-toolbox
 *
 * @property-read string        $id         ID of the document ruleset.
 * @property-read array<string> $htmlFormat HTML format that this DocRuleset applies to.
 */
abstract class DocRuleset
{
    /**
     * ID of the document ruleset.
     *
     * This needs to be overridden in the extending class.
     *
     * @var string
     */
    const ID = '[document ruleset base class]';

    /**
     * Spec data of the document ruleset.
     *
     * @var array
     */
    const SPEC = [];

    /**
     * Get the ID of the document ruleset.
     *
     * @return string ID of the document ruleset.
     */
    public function getId()
    {
        return static::ID;
    }

    /**
     * Check whether a given spec rule is present.
     *
     * @param string $docRulesetName Name of the spec rule to check for.
     * @return bool Whether the given spec rule is contained in the spec.
     */
    public function has($docRulesetName)
    {
        return array_key_exists($docRulesetName, static::SPEC);
    }

    /**
     * Get a specific spec rule.
     *
     * @param string $docRulesetName Name of the spec rule to get.
     * @return array Spec rule data that was requested.
     */
    public function get($docRulesetName)
    {
        if (!$this->has($docRulesetName)) {
            throw InvalidSpecRuleName::forSpecRuleName($docRulesetName);
        }

        return static::SPEC[$docRulesetName];
    }

    /**
     * Magic getter to return the spec rules.
     *
     * @param string $docRulesetName Name of the spec rule to return.
     * @return mixed Value of the spec rule.
     */
    public function __get($docRulesetName)
    {
        switch ($docRulesetName) {
            case 'id':
                return static::ID;
            case SpecRule::HTML_FORMAT:
                return array_key_exists($docRulesetName, static::SPEC) ? static::SPEC[$docRulesetName] : [];
            default:
                if (!array_key_exists($docRulesetName, static::SPEC)) {
                    throw InvalidSpecRuleName::forSpecRuleName($docRulesetName);
                }

                return static::SPEC[$docRulesetName];
        }
    }
}
