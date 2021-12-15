<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Section;

use AmpProject\Exception\InvalidCssRulesetName;
use AmpProject\Exception\InvalidFormat;
use AmpProject\Format;
use AmpProject\Validator\Spec\CssRuleset;
use AmpProject\Validator\Spec\IterableSection;
use AmpProject\Validator\Spec\Iteration;

/**
 * The CssRulesets section defines the validation rules that apply to the CSS of a document.
 *
 * @package ampproject/amp-toolbox
 *
 * @method CssRuleset parentCurrent()
 */
final class CssRulesets implements IterableSection
{
    use Iteration {
        Iteration::current as parentCurrent;
    }

    /**
     * Mapping of CSS ruleset ID to CSS ruleset implementation.
     *
     * @var array<string>
     */
    const CSS_RULESETS = [
        CssRuleset\AmpNoTransformed::ID => CssRuleset\AmpNoTransformed::class,
        CssRuleset\AmpTransformed::ID => CssRuleset\AmpTransformed::class,
        CssRuleset\Amp4ads::ID => CssRuleset\Amp4ads::class,
        CssRuleset\Amp4emailDataCssStrict::ID => CssRuleset\Amp4emailDataCssStrict::class,
        CssRuleset\Amp4emailNoDataCssStrict::ID => CssRuleset\Amp4emailNoDataCssStrict::class,
    ];

    /**
     * Mapping of AMP format to array of CSS ruleset IDs.
     *
     * This is used to optimize querying by AMP format.
     *
     * @var array<array<string>>
     */
    const BY_FORMAT = [
        Format::AMP => [
            CssRuleset\AmpNoTransformed::ID,
            CssRuleset\AmpTransformed::ID,
        ],
        Format::AMP4ADS => [
            CssRuleset\Amp4ads::ID,
        ],
        Format::AMP4EMAIL => [
            CssRuleset\Amp4emailDataCssStrict::ID,
            CssRuleset\Amp4emailNoDataCssStrict::ID,
        ],
    ];

    /**
     * Cache of instantiated CssRuleset objects.
     *
     * @var array<CssRuleset>
     */
    private $cssRulesetsCache = [];

    /**
     * Array used for storing the iteration index in.
     *
     * @var array<string>|null
     */
    private $iterationArray;

    /**
     * Get a CSS ruleset by its CSS ruleset ID.
     *
     * @param string $cssRulesetId CSS ruleset ID to get the collection of CSS rulesets for.
     * @return CssRuleset Requested CSS ruleset.
     * @throws InvalidCssRulesetName If an invalid CSS ruleset name is requested.
     */
    public function get($cssRulesetId)
    {
        if (!array_key_exists($cssRulesetId, self::CSS_RULESETS)) {
            throw InvalidCssRulesetName::forCssRulesetName($cssRulesetId);
        }

        if (array_key_exists($cssRulesetId, $this->cssRulesetsCache)) {
            return $this->cssRulesetsCache[$cssRulesetId];
        }

        $cssRulesetClassName = self::CSS_RULESETS[$cssRulesetId];

        /** @var CssRuleset $cssRuleset */
        $cssRuleset = new $cssRulesetClassName();

        $this->cssRulesetsCache[$cssRulesetId] = $cssRuleset;

        return $cssRuleset;
    }

    /**
     * Get a collection of CSS rulesets for a given AMP HTML format name.
     *
     * @param string $format AMP HTML format to get the CSS rulesets for.
     * @return array<CssRuleset> Array of CSS rulesets matching the requested AMP HTML format.
     * @throws InvalidFormat If an invalid AMP HTML format is requested.
     */
    public function byFormat($format)
    {
        if (!array_key_exists($format, self::BY_FORMAT)) {
            throw InvalidFormat::forFormat($format);
        }

        $cssRulesetIds = self::BY_FORMAT[$format];
        if (!is_array($cssRulesetIds)) {
            $cssRulesetIds = [$cssRulesetIds];
        }

        $cssRulesets = [];
        foreach ($cssRulesetIds as $cssRulesetId) {
            $cssRulesets[] = $this->get($cssRulesetId);
        }

        return $cssRulesets;
    }

    /**
     * Get the list of available keys.
     *
     * @return array<string> Array of available keys.
     */
    public function getAvailableKeys()
    {
        return array_keys(self::CSS_RULESETS);
    }

    /**
     * Find the instantiated object for the current key.
     *
     * This should use its own caching mechanism as needed.
     *
     * Ideally, current() should be overridden as well to provide the correct object type-hint.
     *
     * @param string $key Key to retrieve the instantiated object for.
     * @return CssRuleset Instantiated object for the current key.
     */
    public function findByKey($key)
    {
        return $this->get($key);
    }

    /**
     * Return the current iterable object.
     *
     * @return CssRuleset CssRuleset object.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->parentCurrent();
    }
}
