<?php

namespace AmpProject\Dom;

/**
 * Unique ID manager.
 *
 * @package ampproject/amp-toolbox
 */
final class UniqueIdManager
{
    /**
     * Store the current index by prefix.
     *
     * This is used to generate unique-per-prefix IDs.
     *
     * @var int[]
     */
    private $indexCounter = [];

    /**
     * Get auto-incremented ID unique to this class's instantiation.
     *
     * @param string $prefix Prefix.
     * @return string ID.
     */
    public function getUniqueId($prefix = '')
    {
        if (array_key_exists($prefix, $this->indexCounter)) {
            ++$this->indexCounter[$prefix];
        } else {
            $this->indexCounter[$prefix] = 0;
        }
        $uniqueId = (string)$this->indexCounter[$prefix];
        if ($prefix) {
            $uniqueId = "{$prefix}-{$uniqueId}";
        }
        return $uniqueId;
    }
}
