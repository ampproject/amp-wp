<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

use AmpProject\Exception\InvalidSpecRuleName;

/**
 * The base class for a single CSSRuleset object that provides the CSS validation rules for a specific format.
 *
 * @package ampproject/amp-toolbox
 *
 * @property-read string        $id                         ID of the CSS ruleset.
 * @property-read bool          $allowAllDeclarationInStyle
 * @property-read bool          $allowImportant
 * @property-read bool          $expandVendorPrefixes
 * @property-read bool          $maxBytesIsWarning
 * @property-read bool          $urlBytesIncluded
 * @property-read array<string> $declarationListSvg
 * @property-read array<string> $disabledBy
 * @property-read array<string> $enabledBy
 * @property-read array<array>  $fontUrlSpec
 * @property-read array<string> $htmlFormat
 * @property-read array<array>  $imageUrlSpec
 */
class CssRuleset
{
    /**
     * ID of the CSS ruleset.
     *
     * This needs to be overridden in the extending class.
     *
     * @var string
     */
    const ID = '[css ruleset base class]';

    /**
     * Spec data of the CSS ruleset.
     *
     * @var array
     */
    const SPEC = [];

    /**
     * Get the ID of the CSS ruleset.
     *
     * @return string ID of the CSS ruleset.
     */
    public function getId()
    {
        return static::ID;
    }

    /**
     * Check whether a given spec rule is present.
     *
     * @param string $cssRulesetName Name of the spec rule to check for.
     * @return bool Whether the given spec rule is contained in the spec.
     */
    public function has($cssRulesetName)
    {
        return array_key_exists($cssRulesetName, static::SPEC);
    }

    /**
     * Get a specific spec rule.
     *
     * @param string $cssRulesetName Name of the spec rule to get.
     * @return array Spec rule data that was requested.
     */
    public function get($cssRulesetName)
    {
        if (!$this->has($cssRulesetName)) {
            throw InvalidSpecRuleName::forSpecRuleName($cssRulesetName);
        }

        return static::SPEC[$cssRulesetName];
    }

    /**
     * Magic getter to return the spec rules.
     *
     * @param string $cssRulesetName Name of the spec rule to return.
     * @return mixed Value of the spec rule.
     */
    public function __get($cssRulesetName)
    {
        switch ($cssRulesetName) {
            case 'id':
                return static::ID;
            case SpecRule::ALLOW_ALL_DECLARATION_IN_STYLE:
            case SpecRule::ALLOW_IMPORTANT:
            case SpecRule::EXPAND_VENDOR_PREFIXES:
            case SpecRule::MAX_BYTES_IS_WARNING:
            case SpecRule::URL_BYTES_INCLUDED:
                return array_key_exists($cssRulesetName, static::SPEC) ? static::SPEC[$cssRulesetName] : false;
            case SpecRule::DECLARATION_LIST_SVG:
            case SpecRule::DISABLED_BY:
            case SpecRule::ENABLED_BY:
            case SpecRule::FONT_URL_SPEC:
            case SpecRule::HTML_FORMAT:
            case SpecRule::IMAGE_URL_SPEC:
                return array_key_exists($cssRulesetName, static::SPEC) ? static::SPEC[$cssRulesetName] : [];
            default:
                if (!array_key_exists($cssRulesetName, static::SPEC)) {
                    throw InvalidSpecRuleName::forSpecRuleName($cssRulesetName);
                }

                return static::SPEC[$cssRulesetName];
        }
    }
}
