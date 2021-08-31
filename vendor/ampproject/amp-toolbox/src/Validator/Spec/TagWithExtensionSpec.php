<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec;

/**
 * An tag specification that provides the script for an AMP extension.
 *
 * @package ampproject/amp-toolbox
 */
interface TagWithExtensionSpec
{
    /**
     * Get the name of the extension.
     *
     * @return string Extension name.
     */
    public function getExtensionName();

    /**
     * Get the latest available version of the extension.
     *
     * @return string Latest available version.
     */
    public function getLatestVersion();

    /**
     * Get the type of the extension.
     *
     * @return string Extension type.
     */
    public function getExtensionType();
}
