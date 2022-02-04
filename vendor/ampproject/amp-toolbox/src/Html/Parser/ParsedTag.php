<?php

namespace AmpProject\Html\Parser;

use AmpProject\ScriptReleaseVersion;
use AmpProject\Str;

/**
 * The Html parser makes method calls with ParsedTags as arguments.
 *
 * @package ampproject/amp-toolbox
 */
final class ParsedTag
{
    /**
     * Name of the parsed tag.
     *
     * @var string
     */
    private $tagName;

    /**
     * Associative array of attributes.
     *
     * @var array<ParsedAttribute>
     */
    private $attributes = [];

    /**
     * Lazily allocated map from attribute name to value.
     *
     * @var array<string>|null
     */
    private $attributesByKey;

    /**
     * State of a script tag.
     *
     * @var ScriptTag
     */
    private $scriptTag;

    /**
     * ParsedTag constructor.
     *
     * @param string $tagName               Name of the parsed tag.
     * @param array  $alternatingAttributes Optional. Array of alternating (name, value) pairs.
     */
    public function __construct($tagName, $alternatingAttributes = [])
    {
        /*
         * Tag and Attribute names are case-insensitive. For error messages, we would like to use lower-case names as
         * they read a little nicer. However, in validator environments where the parsing is done by the actual browser,
         * the DOM API returns tag names in upper case. We stick with this convention for tag names, for performance,
         * but convert to lower when producing error messages. Error messages aren't produced in latency sensitive
         * contexts.
         */

        if (! is_array($alternatingAttributes)) {
            $alternatingAttributes = [];
        }

        $this->tagName = Str::toUpperCase($tagName);

        // Convert attribute names to lower case, not values, which are case-sensitive.
        $count = count($alternatingAttributes);
        for ($index = 0; $index < $count; $index += 2) {
            $name = Str::toLowerCase($alternatingAttributes[$index]);
            $value = $alternatingAttributes[$index + 1];
            // Our html parser repeats the key as the value if there is no value. We
            // replace the value with an empty string instead in this case.
            if ($name === $value) {
                $value = '';
            }
            $this->attributes[] = new ParsedAttribute($name, $value);
        }

        // Sort the attribute array by (lower case) name.
        usort($this->attributes, function (ParsedAttribute $a, ParsedAttribute $b) {
            if (PHP_MAJOR_VERSION < 7 && $a->name() === $b->name()) {
                // Hack required for PHP 5.6, as it does not maintain stable order for equal items.
                // See https://bugs.php.net/bug.php?id=69158.
                // To get around this, we compare the index within $this->attributes instead to maintain existing order.
                return strcmp(array_search($a, $this->attributes, true), array_search($b, $this->attributes, true));
            }

            return strcmp($a->name(), $b->name());
        });

        $this->scriptTag = new ScriptTag($this->tagName, $this->attributes);
    }

    /**
     * Get the lower-case tag name.
     *
     * @return string Lower-case tag name.
     */
    public function lowerName()
    {
        return Str::toLowerCase($this->tagName);
    }

    /**
     * Get the upper-case tag name.
     *
     * @return string Upper-case tag name.
     */
    public function upperName()
    {
        return $this->tagName;
    }

    /**
     * Returns an array of attributes.
     *
     * Each attribute has two fields: name and value. Name is always lower-case, value is the case from the original
     * document. Values are unescaped.
     *
     * @return array<ParsedAttribute>
     */
    public function attributes()
    {
        return $this->attributes;
    }

    /**
     * Returns an object mapping attribute name to attribute value.
     *
     * This is populated lazily, as it's not used for most tags.
     *
     * @return array<string>
     * */
    public function attributesByKey()
    {
        if ($this->attributesByKey === null) {
            $this->attributesByKey = [];
            foreach ($this->attributes as $attribute) {
                $this->attributesByKey[$attribute->name()] = $attribute->value();
            }
        }

        return $this->attributesByKey;
    }

    /**
     * Returns a duplicate attribute name if the tag contains two attributes named the same, but with different
     * attribute values.
     *
     * Same attribute name AND value is OK. Returns null if there are no such duplicate attributes.
     *
     * @return string|null
     */
    public function hasDuplicateAttributes()
    {
        $lastAttributeName  = '';
        $lastAttributeValue = '';

        foreach ($this->attributes as $attribute) {
            if (
                $lastAttributeName === $attribute->name()
                &&
                $lastAttributeValue !== $attribute->value()
            ) {
                return $attribute->name();
            }

            $lastAttributeName  = $attribute->name();
            $lastAttributeValue = $attribute->value();
        }

        return null;
    }

    /**
     * Removes duplicate attributes from the attribute list.
     *
     * This is consistent with HTML5 parsing error handling rules, only the first attribute with each attribute name is
     * considered, the remainder are ignored.
     */
    public function dedupeAttributes()
    {
        $newAttributes     = [];
        $lastAttributeName = '';

        foreach ($this->attributes as $attribute) {
            if ($lastAttributeName !== $attribute->name()) {
                $newAttributes[] = $attribute;
            }
            $lastAttributeName = $attribute->name();
        }

        $this->attributes = $newAttributes;
    }

    /**
     * Returns the value of a given attribute name. If it does not exist then returns null.
     *
     * @param string $name Name of the attribute.
     * @return string|null Value of the attribute, or null if it does not exist.
     */
    public function getAttributeValueOrNull($name)
    {
        $attributesByKey = $this->attributesByKey();

        return array_key_exists($name, $attributesByKey) ? $attributesByKey[$name] : null;
    }

    /**
     * Returns the script release version, otherwise ScriptReleaseVersion::UNKNOWN.
     *
     * @return ScriptReleaseVersion
     */
    public function getScriptReleaseVersion()
    {
        return $this->scriptTag->releaseVersion();
    }

    /**
     * Tests if this tag is a script with a src of an AMP domain.
     *
     * @return bool Whether this tag is a script with a src of an AMP domain.
     */
    public function isAmpDomain()
    {
        return $this->scriptTag->isAmpDomain();
    }

    /**
     * Tests if this is the AMP runtime script tag.
     *
     * @return bool Whether this is the AMP runtime script tag.
     */
    public function isAmpRuntimeScript()
    {
        return $this->scriptTag->isRuntime();
    }

    /**
     * Tests if this is an extension script tag.
     *
     * @return bool Whether this is an extension script tag.
     */
    public function isExtensionScript()
    {
        return $this->scriptTag->isExtension();
    }
}
