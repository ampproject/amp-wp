<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

use AmpProject\Html\Attribute;

/**
 * A tag specification that provides the script for an AMP extension.
 *
 * @package ampproject/amp-toolbox
 */
abstract class TagWithExtensionSpec extends Tag
{
    /**
     * Array of extension spec rules.
     *
     * @var array
     */
    const EXTENSION_SPEC = [];

    /**
     * Latest version of the extension.
     *
     * @var string
     */
    const LATEST_VERSION = '';

    /**
     * Meta data about the specific versions.
     *
     * @var array
     */
    const VERSIONS_META = [];

    /**
     * Get the name of the extension.
     *
     * @return string Extension name.
     */
    public function getExtensionName()
    {
        if (! array_key_exists(SpecRule::NAME, static::EXTENSION_SPEC)) {
            return 'unknown';
        }

        return static::EXTENSION_SPEC[SpecRule::NAME];
    }

    /**
     * Get the latest available version of the extension.
     *
     * @return string Latest available version.
     */
    public function getLatestVersion()
    {
        return static::LATEST_VERSION;
    }

    /**
     * Get the type of the extension.
     *
     * @return string Extension type.
     */
    public function getExtensionType()
    {
        if (! array_key_exists(SpecRule::EXTENSION_TYPE, static::EXTENSION_SPEC)) {
            return Attribute::CUSTOM_ELEMENT;
        }

        return str_replace('_', '-', strtolower(static::EXTENSION_SPEC[SpecRule::EXTENSION_TYPE]));
    }

    /**
     * Get the associative array of versions meta data.
     *
     * @return array
     */
    public function getVersionsMeta()
    {
        return static::VERSIONS_META;
    }
}
