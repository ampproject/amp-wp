<?php

namespace AmpProject\Html\Parser;

use AmpProject\Amp;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use AmpProject\ScriptReleaseVersion;
use AmpProject\Str;

/**
 * Represents the state of a script tag.
 *
 * @package ampproject/amp-toolbox
 */
final class ScriptTag
{
    /**
     * Name of the tag.
     *
     * @var string
     */
    private $tagName;

    /**
     * Array of parsed attributes.
     *
     * @var array<ParsedAttribute>
     */
    private $attributes;

    /**
     * Lazily evaluated collection of properties about the script tag.
     *
     * @var array|null
     */
    private $parsedProperties;

    /**
     * Standard and Nomodule JavaScript.
     *
     * Examples:
     * - v0.js
     * - v0/amp-ad-0.1.js
     *
     * @var string
     */
    const STANDARD_SCRIPT_PATH_REGEX = '/(v0|v0/amp-[a-z0-9-]*-[a-z0-9.]*)\\.js$/i';

    /**
     * LTS and Nomodule LTS JavaScript.
     *
     * Examples:
     * - lts/v0.js
     * - lts/v0/amp-ad-0.1.js
     *
     * @var string
     */
    const LTS_SCRIPT_PATH_REGEX = '/lts/(v0|v0/amp-[a-z0-9-]*-[a-z0-9.]*)\\.js$/i';

    /**
     * Module JavaScript.
     *
     * Examples:
     * - v0.mjs
     * - amp-ad-0.1.mjs
     *
     * @var string
     */
    const MODULE_SCRIPT_PATH_REGEX = '/(v0|v0/amp-[a-z0-9-]*-[a-z0-9.]*)\\.mjs$/i';

    /**
     * Module LTS JavaScript.
     *
     * Examples:
     * - lts/v0.mjs
     * - lts/v0/amp-ad-0.1.mjs
     *
     * @var string
     */
    const MODULE_LTS_SCRIPT_PATH_REGEX = '/lts/(v0|v0/amp-[a-z0-9-]*-[a-z0-9.]*)\\.mjs$/i';

    /**
     * Runtime JavaScript.
     *
     * Examples:
     * - v0.js
     * - v0.mjs
     * - v0.mjs?f=sxg
     * - lts/v0.js
     * - lts/v0.js?f=sxg
     * -lts/v0.mjs
     *
     * @var string
     */
    const RUNTIME_SCRIPT_PATH_REGEX = '/(lts/)?v0\\.m?js(\\?f=sxg)?/i';

    /**
     * ScriptTag constructor.
     *
     * @param string                 $tagName    Name of the tag.
     * @param array<ParsedAttribute> $attributes Array of parsed attributes.
     */
    public function __construct($tagName, $attributes)
    {
        $this->tagName    = $tagName;
        $this->attributes = $attributes;
    }

    /**
     * Returns the script release version, otherwise ScriptReleaseVersion::UNKNOWN.
     *
     * @return ScriptReleaseVersion
     */
    public function releaseVersion()
    {
        if ($this->tagName !== Tag::SCRIPT) {
            return ScriptReleaseVersion::UNKNOWN();
        }

        $properties = $this->parseAttributes();

        return $properties['releaseVersion'];
    }

    /**
     * Tests if this tag is a script with a src of an AMP domain.
     *
     * @return bool Whether this tag is a script with a src of an AMP domain.
     */
    public function isAmpDomain()
    {
        if ($this->tagName !== Tag::SCRIPT) {
            return false;
        }

        $properties = $this->parseAttributes();

        return $properties['isAmpDomain'];
    }

    /**
     * Tests if this is the AMP runtime script tag.
     *
     * @return bool Whether this is the AMP runtime script tag.
     */
    public function isRuntime()
    {
        if ($this->tagName !== Tag::SCRIPT) {
            return false;
        }

        $properties = $this->parseAttributes();

        return $properties['isRuntime'];
    }

    /**
     * Tests if this is an extension script tag.
     *
     * @return bool Whether this is an extension script tag.
     */
    public function isExtension()
    {
        if ($this->tagName !== Tag::SCRIPT) {
            return false;
        }

        $properties = $this->parseAttributes();

        return $properties['isExtension'];
    }

    /**
     * Parse attributes to determine script properties.
     *
     * @return array Associative array of parsed properties.
     */
    private function parseAttributes()
    {
        if ($this->parsedProperties !== null) {
            return $this->parsedProperties;
        }

        $properties = [
            'isAsync'     => false,
            'isModule'    => false,
            'isNomodule'  => false,
            'isExtension' => false,
            'path'        => '',
            'src'         => '',
        ];

        foreach ($this->attributes as $attribute) {
            if ($attribute->name() === Attribute::ASYNC) {
                $properties['isAsync'] = true;
            } elseif (
                $attribute->name() === Attribute::CUSTOM_ELEMENT
                ||
                $attribute->name() === Attribute::CUSTOM_TEMPLATE
                ||
                $attribute->name() === Attribute::HOST_SERVICE
            ) {
                $properties['isExtension'] = true;
            } elseif ($attribute->name() === Attribute::NOMODULE) {
                $properties['isNomodule'] = true;
            } elseif ($attribute->name() === Attribute::SRC) {
                $properties['src'] = $attribute->value();
            } elseif (
                $attribute->name() === Attribute::TYPE
                &&
                $attribute->value() === Attribute::TYPE_MODULE
            ) {
                $properties['isModule'] = true;
            }
        }

        // Determine if this has a valid AMP domain and separate the path from the attribute 'src'.
        if (Str::position($properties['src'], Amp::CACHE_ROOT_URL) === 0) {
            $properties['isAmpDomain'] = true;
            $properties['path']        = Str::substring($properties['src'], Str::length(Amp::CACHE_ROOT_URL));

            // Only look at script tags that have attribute 'async'.
            if ($properties['isAsync']) {
                // Determine if this is the AMP Runtime.
                if (
                    ! $properties['isExtension']
                    &&
                    Str::regexMatch(self::RUNTIME_SCRIPT_PATH_REGEX, $properties['path'])
                ) {
                    $properties['isRuntime'] = true;
                }

                // Determine the release version (LTS, module, standard, etc).
                if (
                    (
                        $properties['isModule']
                        &&
                        Str::regexMatch(self::MODULE_LTS_SCRIPT_PATH_REGEX, $properties['path'])
                    ) || (
                        $properties['isNomodule']
                        &&
                        Str::regexMatch(self::LTS_SCRIPT_PATH_REGEX, $properties['path'])
                    )
                ) {
                    $properties['releaseVersion'] = ScriptReleaseVersion::MODULE_NOMODULE_LTS();
                } elseif (
                    (
                        $properties['isModule']
                        &&
                        Str::regexMatch(self::MODULE_SCRIPT_PATH_REGEX, $properties['path'])
                    ) || (
                        $properties['isNomodule']
                        &&
                        Str::regexMatch(self::STANDARD_SCRIPT_PATH_REGEX, $properties['path'])
                    )
                ) {
                    $properties['releaseVersion'] = ScriptReleaseVersion::MODULE_NOMODULE();
                } elseif (Str::regexMatch(self::LTS_SCRIPT_PATH_REGEX, $properties['path'])) {
                    $properties['releaseVersion'] = ScriptReleaseVersion::LTS();
                } elseif (Str::regexMatch(self::STANDARD_SCRIPT_PATH_REGEX, $properties['path'])) {
                    $properties['releaseVersion'] = ScriptReleaseVersion::STANDARD();
                } else {
                    $properties['releaseVersion'] = ScriptReleaseVersion::UNKNOWN();
                }
            }
        }

        $this->parsedProperties = $properties;

        return $this->parsedProperties;
    }
}
