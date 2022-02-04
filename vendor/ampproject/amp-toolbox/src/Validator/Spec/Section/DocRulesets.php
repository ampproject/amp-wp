<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Section;

use AmpProject\Exception\InvalidDocRulesetName;
use AmpProject\Exception\InvalidFormat;
use AmpProject\Format;
use AmpProject\Validator\Spec\DocRuleset;
use AmpProject\Validator\Spec\IterableSection;
use AmpProject\Validator\Spec\Iteration;

/**
 * The DocRulesets section defines the validation rules that apply to an entire document.
 *
 * @package ampproject/amp-toolbox
 *
 * @method DocRuleset parentCurrent()
 */
final class DocRulesets implements IterableSection
{
    use Iteration {
        Iteration::current as parentCurrent;
    }

    /**
     * Mapping of document ruleset ID to document ruleset implementation.
     *
     * @var array<string>
     */
    const DOC_RULESETS = [
        DocRuleset\Amp4email::ID => DocRuleset\Amp4email::class,
    ];

    /**
     * Mapping of AMP format to array of document ruleset IDs.
     *
     * This is used to optimize querying by AMP format.
     *
     * @var array<array<string>>
     */
    const BY_FORMAT = [
        Format::AMP4EMAIL => [
            DocRuleset\Amp4email::ID,
        ],
    ];

    /**
     * Cache of instantiated DocRuleset objects.
     *
     * @var array<DocRuleset>
     */
    private $docRulesetsCache = [];

    /**
     * Array used for storing the iteration index in.
     *
     * @var array<string>|null
     */
    private $iterationArray;

    /**
     * Get a document ruleset by its document ruleset ID.
     *
     * @param string $docRulesetId document ruleset ID to get the collection of document rulesets for.
     * @return DocRuleset Requested document ruleset.
     * @throws InvalidDocRulesetName If an invalid document ruleset name is requested.
     */
    public function get($docRulesetId)
    {
        if (!array_key_exists($docRulesetId, self::DOC_RULESETS)) {
            throw InvalidDocRulesetName::forDocRulesetName($docRulesetId);
        }

        if (array_key_exists($docRulesetId, $this->docRulesetsCache)) {
            return $this->docRulesetsCache[$docRulesetId];
        }

        $docRulesetClassName = self::DOC_RULESETS[$docRulesetId];

        /** @var DocRuleset $docRuleset */
        $docRuleset = new $docRulesetClassName();

        $this->docRulesetsCache[$docRulesetId] = $docRuleset;

        return $docRuleset;
    }

    /**
     * Get a collection of document rulesets for a given AMP HTML format name.
     *
     * @param string $format AMP HTML format to get the document rulesets for.
     * @return array<DocRuleset> Array of document rulesets matching the requested AMP HTML format.
     * @throws InvalidFormat If an invalid AMP HTML format is requested.
     */
    public function byFormat($format)
    {
        if (!array_key_exists($format, self::BY_FORMAT)) {
            throw InvalidFormat::forFormat($format);
        }

        $docRulesetIds = self::BY_FORMAT[$format];
        if (!is_array($docRulesetIds)) {
            $docRulesetIds = [$docRulesetIds];
        }

        $docRulesets = [];
        foreach ($docRulesetIds as $docRulesetId) {
            $docRulesets[] = $this->get($docRulesetId);
        }

        return $docRulesets;
    }

    /**
     * Get the list of available keys.
     *
     * @return array<string> Array of available keys.
     */
    public function getAvailableKeys()
    {
        return array_keys(self::DOC_RULESETS);
    }

    /**
     * Find the instantiated object for the current key.
     *
     * This should use its own caching mechanism as needed.
     *
     * Ideally, current() should be overridden as well to provide the correct object type-hint.
     *
     * @param string $key Key to retrieve the instantiated object for.
     * @return DocRuleset Instantiated object for the current key.
     */
    public function findByKey($key)
    {
        return $this->get($key);
    }

    /**
     * Return the current iterable object.
     *
     * @return DocRuleset DocRuleset object.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->parentCurrent();
    }
}
