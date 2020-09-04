<?php

namespace AmpProject\Optimizer;

/**
 * CSS rule that provides semantic handling of media queries, selectors and properties.
 *
 * This is used in conjunction with CssRules for deduplication of CSS when adding styles during transformations.
 *
 * Note: This is a simplistic representation of CSS rules built for a specific purpose.
 * Make sure it supports a given use case before including in new code!
 *
 * @package ampproject/optimizer
 */
final class CssRule
{

    /**
     * Placeholder to use in the rule to denote an ID that has yet to be defined.
     *
     * Use applyId() to finalize the CSS rule.
     *
     * @var string.
     */
    const ID_PLACEHOLDER = '__ID__';

    /**
     * Selector(s) to use for the CSS rule.
     *
     * @var string[]
     */
    private $selectors;

    /**
     * Properties to apply to the selector(s).
     *
     * @var string[]
     */
    private $properties;

    /**
     * Media query that wraps the CSS rule.
     *
     * @var string
     */
    private $mediaQuery = '';

    /**
     * Rendered CSS cache.
     *
     * @var string|null
     */
    private $renderedCss;

    /**
     * Byte count cache.
     *
     * @var int|null
     */
    private $byteCount;

    /**
     * Instantiate a CssRule object.
     *
     * @param string|string[] $selectors  One or more selectors to use.
     * @param string|string[] $properties One or more properties to apply to the selector(s).
     */
    public function __construct($selectors, $properties)
    {
        $this->selectors = array_values(
            array_unique(
                array_filter(
                    array_map(
                        [$this, 'normalizeSelector'],
                        $this->separateSelectors($selectors)
                    )
                )
            )
        );

        $this->properties = array_values(
            array_unique(
                array_filter(
                    array_map(
                        [$this, 'normalizeProperty'],
                        $this->separateProperties($properties)
                    )
                )
            )
        );

        sort($this->properties);
    }

    /**
     * Create a new CSS rule that is wrapped in a media query.
     *
     * @param string $mediaQuery Media query to wrap the CSS rule in.
     * @param string|string[] $selectors  One or more selectors to use.
     * @param string|string[] $properties One or more properties to apply to the selector(s).
     * @return self CSS rule wrapped in a media query.
     */
    public static function withMediaQuery($mediaQuery, $selectors, $properties)
    {
        $cssRule = new self($selectors, $properties);
        $cssRule->mediaQuery = $mediaQuery;
        return $cssRule;
    }

    /**
     * Get the selector(s) for this CSS rule.
     *
     * @return string[] Selector(s) of the CSS rule.
     */
    public function getSelectors()
    {
        return $this->selectors;
    }

    /**
     * Get the properties for this CSS rule.
     *
     * @return string[] Properties of the CSS rule.
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Get the media query for this CSS rule.
     *
     * @return string Media query for the CSS rule or an empty string if none is set.
     */
    public function getMediaQuery()
    {
        return $this->mediaQuery;
    }

    /**
     * Get the CSS for this CSS rule.
     *
     * @return string CSS for this CSS rule.
     */
    public function getCss()
    {
        if ($this->renderedCss === null) {
            $selectors = implode(',', $this->selectors);

            $properties = implode(
                ';',
                array_map(
                    static function ($property) {
                        return trim($property, " \t\n\r\0\x0B;");
                    },
                    $this->properties
                )
            );

            if (empty($selectors) || empty($properties)) {
                $this->renderedCss = '';
            } else {
                $this->renderedCss = "{$selectors}{{$properties}}";

                if (! empty($this->mediaQuery)) {
                    $this->renderedCss = "{$this->mediaQuery}{{$this->renderedCss}}";
                }
            }
        }

        return $this->renderedCss;
    }

    /**
     * Apply the provided ID across all ID placeholders.
     *
     * @param string $id ID to apply.
     * @return self
     */
    public function applyID($id)
    {
        $replacement_callback = static function ($css) use ($id) {
            return str_replace(self::ID_PLACEHOLDER, $id, $css);
        };

        $this->selectors  = array_map($replacement_callback, $this->selectors);
        $this->properties = array_map($replacement_callback, $this->properties);

        // Reset caches so they will need to be rebuilt.
        $this->renderedCss = null;
        $this->byteCount   = null;

        return $this;
    }

    /**
     * Check if the CSS rule can be merged with another provided CSS rule.
     *
     * @param CssRule $that CSS rule to check against.
     * @return bool Whether the two CSS rules can be merged.
     */
    public function canBeMerged(CssRule $that)
    {
        if ($this->mediaQuery !== $that->mediaQuery) {
            return false;
        }

        if (
            count($this->properties) !== count($that->properties)
            || array_diff($this->properties, $that->properties)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Merge this CSS rule with another CSS rule.
     *
     * This should only be done to same-properties rules within the same media query,
     * as the result will be wild otherwise.
     *
     * @param CssRule $that CSS rule to merge the current one with.
     * @return CssRule Merged Css rule.
     */
    public function mergeWith(CssRule $that)
    {
        $cssRule = new self(
            array_merge($this->selectors, $that->selectors),
            array_merge($this->properties, $that->properties)
        );

        $cssRule->mediaQuery = $this->mediaQuery;

        return $cssRule;
    }

    /**
     * Get the byte count for the CSS rule.
     *
     * @return int Byte count of the CSS rule.
     */
    public function getByteCount()
    {
        if ($this->byteCount === null) {
            $this->byteCount = strlen($this->getCss());
        }

        return $this->byteCount;
    }

    /**
     * Normalize a single selector.
     *
     * @param string $selector Selector to normalize.
     * @return string Normalized selector.
     */
    private function normalizeSelector($selector)
    {
        // Turn all series of whitespace into single spaces.
        $selector = preg_replace('/\s+/', ' ', $selector);

        // Remove spaces around selector qualifiers to keep properties compact.
        $selector = preg_replace('/ ?([>+~]) ?/', '$1', $selector);

        // Remove leading and trailing whitespace and commas.
        $selector = trim($selector, " \t\n\r\0\x0B;");

        return $selector;
    }

    /**
     * Normalize single property.
     *
     * @param string $property Property to normalize.
     * @return string Normalized property.
     */
    private function normalizeProperty($property)
    {
        // Turn all series of whitespace into single spaces.
        $property = preg_replace('/\s+/', ' ', $property);

        // Remove spaces around colons and semicolons to keep properties compact.
        $property = preg_replace('/ ?([:;]) ?/', '$1', $property);

        // Deduplicate semicolons.
        $property = preg_replace('/([;]+)/', ';', $property);

        // Remove leading and trailing whitespace and semicolons.
        $property = trim($property, " \t\n\r\0\x0B;");

        return $property;
    }

    /**
     * Separate selectors into individual values.
     *
     * @param string|string[]|array[] $selectors Selectors to separate.
     * @return string[] Separated selectors.
     */
    private function separateSelectors($selectors)
    {
        $separatedSelectors = [];

        foreach ((array)$selectors as $selectorString) {
            if (is_array($selectorString)) {
                $separatedSelectors = array_merge($separatedSelectors, $this->separateSelectors($selectorString));
            } else {
                $separatedSelectors = array_merge($separatedSelectors, explode(',', $selectorString));
            }
        }

        return $separatedSelectors;
    }

    /**
     * Separate properties into individual values.
     *
     * @param string|string[]|array[] $properties Properties to separate.
     * @return string[] Separated properties.
     */
    private function separateProperties($properties)
    {
        $separatedProperties = [];

        foreach ((array)$properties as $propertyString) {
            if (is_array($propertyString)) {
                $separatedProperties = array_merge($separatedProperties, $this->separateProperties($propertyString));
            } else {
                $separatedProperties = array_merge($separatedProperties, explode(';', $propertyString));
            }
        }

        return $separatedProperties;
    }
}
