<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Section;

use AmpProject\Exception\InvalidListName;
use AmpProject\Validator\Spec;
use AmpProject\Validator\Spec\DescendantTagList;
use AmpProject\Validator\Spec\IterableSection;
use AmpProject\Validator\Spec\Iteration;

/**
 * The DescendantTagLists section provides lists that define the set of allowed descendant tags.
 *
 * @package ampproject/amp-toolbox
 *
 * @method DescendantTagList parentCurrent()
 */
final class DescendantTagLists implements IterableSection
{
    use Iteration {
        Iteration::current as parentCurrent;
    }

    /**
     * Mapping of descendant tag list ID to descendant tag list implementation.
     *
     * @var array<string>
     */
    const DESCENDANT_TAG_LISTS = [
        DescendantTagList\AmpMegaMenuAllowedDescendants::ID => DescendantTagList\AmpMegaMenuAllowedDescendants::class,
        DescendantTagList\AmpNestedMenuAllowedDescendants::ID => DescendantTagList\AmpNestedMenuAllowedDescendants::class,
        DescendantTagList\AmpStoryPlayerAllowedDescendants::ID => DescendantTagList\AmpStoryPlayerAllowedDescendants::class,
        DescendantTagList\AmpStoryBookendAllowedDescendants::ID => DescendantTagList\AmpStoryBookendAllowedDescendants::class,
        DescendantTagList\AmpStorySocialShareAllowedDescendants::ID => DescendantTagList\AmpStorySocialShareAllowedDescendants::class,
        DescendantTagList\AmpStoryCtaLayerAllowedDescendants::ID => DescendantTagList\AmpStoryCtaLayerAllowedDescendants::class,
        DescendantTagList\AmpStoryGridLayerAllowedDescendants::ID => DescendantTagList\AmpStoryGridLayerAllowedDescendants::class,
        DescendantTagList\AmpStoryPageAttachmentAllowedDescendants::ID => DescendantTagList\AmpStoryPageAttachmentAllowedDescendants::class,
    ];

    /**
     * Cache of instantiated descendant tag list objects.
     *
     * @var array<Spec\DescendantTagList>
     */
    private $descendantTagLists = [];

    /**
     * Get a specific descendantTag list.
     *
     * @param string $descendantTagListName Name of the descendant tag list to get.
     * @return Spec\DescendantTagList Descendant tag list with the given descendant tag list name.
     * @throws InvalidListName If an invalid descendant tag list name is requested.
     */
    public function get($descendantTagListName)
    {
        if (!array_key_exists($descendantTagListName, self::DESCENDANT_TAG_LISTS)) {
            throw InvalidListName::forDescendantTagList($descendantTagListName);
        }

        if (array_key_exists($descendantTagListName, $this->descendantTagLists)) {
            return $this->descendantTagLists[$descendantTagListName];
        }

        $descendantTagListClassName = self::DESCENDANT_TAG_LISTS[$descendantTagListName];

        /** @var Spec\DescendantTagList $descendantTagList */
        $descendantTagList = new $descendantTagListClassName();

        $this->descendantTagLists[$descendantTagListName] = $descendantTagList;

        return $descendantTagList;
    }

    /**
     * Get the list of available keys.
     *
     * @return array<string> Array of available keys.
     */
    public function getAvailableKeys()
    {
        return array_keys(self::DESCENDANT_TAG_LISTS);
    }

    /**
     * Find the instantiated object for the current key.
     *
     * This should use its own caching mechanism as needed.
     *
     * Ideally, current() should be overridden as well to provide the correct object type-hint.
     *
     * @param string $key Key to retrieve the instantiated object for.
     * @return object Instantiated object for the current key.
     */
    public function findByKey($key)
    {
        return $this->get($key);
    }

    /**
     * Return the current iterable object.
     *
     * @return DescendantTagList Descendant tag list object.
     */
    public function current()
    {
        return $this->parentCurrent();
    }
}
