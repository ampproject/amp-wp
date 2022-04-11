<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

/**
 * An aggregate tag with extension spec is the aggregate of multiple TagWithExtensionSpec instances that represent the
 * same HTML element.
 *
 * An aggregate tag is a simplification which only provides access to the information that can safely be aggregated.
 *
 * @package ampproject/amp-toolbox
 */
final class AggregateTagWithExtensionSpec extends TagWithExtensionSpec
{
    /**
     * Array of TagWithExtensionSpec instances that this AggregateTag aggregates.
     *
     * @var TagWithExtensionSpec[]
     */
    protected $tags;

    /**
     * Instantiate an AggregateTagWithExtensionSpec object.
     *
     * @param TagWithExtensionSpec[] $tags Array of tags that this AggregateTagWithExtensionSpec aggregates.
     */
    public function __construct($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Get the name of the extension.
     *
     * @return string Extension name.
     */
    public function getExtensionName()
    {
        return $this->tags[0]->getExtensionName();
    }

    /**
     * Get the latest available version of the extension.
     *
     * @return string Latest available version.
     */
    public function getLatestVersion()
    {
        return $this->tags[0]->getLatestVersion();
    }

    /**
     * Get the type of the extension.
     *
     * @return string Extension type.
     */
    public function getExtensionType()
    {
        return $this->tags[0]->getExtensionType();
    }

    /**
     * Get the associative array of versions meta data.
     *
     * @return array
     */
    public function getVersionsMeta()
    {
        $versionsMeta = [];

        foreach ($this->tags as $tag) {
            $versionsMeta = array_merge($versionsMeta, $tag->getVersionsMeta());
        }

        return $versionsMeta;
    }
}
