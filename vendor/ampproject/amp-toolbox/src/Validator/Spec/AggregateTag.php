<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

use AmpProject\Exception\InvalidSpecRuleName;

/**
 * An aggregate tag is the aggregate of multiple Tag instances that represent the same HTML element.
 *
 * An aggregate tag is a simplification which only provides access to the information that can safely be aggregated.
 *
 * @package ampproject/amp-toolbox
 */
final class AggregateTag extends Tag
{
    /**
     * List of spec rules that can be aggregated.
     *
     * @var string[]
     */
    const AGGREGATEABLE_RULES = [
        'id',
        'tagName',
    ];

    /**
     * Array of Tag instances that this AggregateTag aggregates.
     *
     * @var Tag[]
     */
    protected $tags;

    /**
     * Instantiate an AggregateTag object.
     *
     * @param Tag[] $tags Array of tags that this AggregateTag aggregates.
     */
    public function __construct($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Get the ID of the tag.
     *
     * @return string ID of the tag.
     */
    public function getId()
    {
        return 'AggregateTag for ' . $this->get(SpecRule::TAG_NAME);
    }

    /**
     * Check whether a given spec rule is present.
     *
     * Note: For an aggregate tag, this shows only the rules that can unambiguously be aggregated.
     *
     * @param string $specRuleName Name of the spec rule to check for.
     * @return bool Whether the given spec rule is contained in the spec.
     */
    public function has($specRuleName)
    {
        return in_array($specRuleName, static::AGGREGATEABLE_RULES, true);
    }

    /**
     * Get a specific spec rule.
     *
     * Note: For an aggregate tag, this returns only the rules that can unambiguously be aggregated.
     *
     * @param string $specRuleName Name of the spec rule to get.
     * @return mixed Spec rule data that was requested.
     */
    public function get($specRuleName)
    {
        if (!$this->has($specRuleName)) {
            throw InvalidSpecRuleName::forSpecRuleName($specRuleName);
        }

        switch ($specRuleName) {
            case 'id':
                return $this->getId();
            default:
                return $this->tags[0]->get($specRuleName);
        }
    }
}
