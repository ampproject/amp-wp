<?php

namespace AmpProject\Dom;

use AmpProject\Attribute;
use AmpProject\Exception\MaxCssByteCountExceeded;
use AmpProject\Optimizer\CssRule;
use DOMAttr;
use DOMElement;

/**
 * Abstract away some convenience logic for handling DOMElement objects.
 *
 * @property Document $ownerDocument        The ownerDocument for these elements should always be a Dom\Document.
 * @property int      $inlineStyleByteCount Number of bytes that are consumed by the inline style attribute.
 *
 * @package ampproject/amp-toolbox
 */
final class Element extends DOMElement
{

    /**
     * Regular expression pattern to match events and actions within an 'on' attribute.
     *
     * @var string
     */
    const AMP_EVENT_ACTIONS_REGEX_PATTERN = '/((?<event>[^:;]+):(?<actions>(?:[^;,\(]+(?:\([^\)]+\))?,?)+))+?/';

    /**
     * Regular expression pattern to match individual actions within an event.
     *
     * @var string
     */
    const AMP_ACTION_REGEX_PATTERN = '/(?<action>[^(),\s]+(?:\([^\)]+\))?)+/';

    /**
     * Error message to use when the __get() is triggered for an unknown property.
     *
     * @var string
     */
    const PROPERTY_GETTER_ERROR_MESSAGE = 'Undefined property: AmpProject\\Dom\\Element::';

    /**
     * Add CSS styles to the element as an inline style attribute.
     *
     * @param string $style CSS style(s) to add to the inline style attribute.
     * @return DOMAttr|false The new or modified DOMAttr or false if an error occurred.
     * @throws MaxCssByteCountExceeded If the allowed max byte count is exceeded.
     */
    public function addInlineStyle($style)
    {
        $style = trim($style, CssRule::CSS_TRIM_CHARACTERS);

        $existingStyle = (string)trim($this->getAttribute(Attribute::STYLE));
        if (!empty($existingStyle)) {
            $existingStyle = rtrim($existingStyle, ';') . ';';
        }

        $newStyle = $existingStyle . $style;

        return $this->setAttribute(Attribute::STYLE, $newStyle);
    }

    /**
     * Sets or modifies an attribute.
     *
     * @link https://php.net/manual/en/domelement.setattribute.php
     * @param string $name  The name of the attribute.
     * @param string $value The value of the attribute.
     * @return DOMAttr|false The new or modified DOMAttr or false if an error occurred.
     * @throws MaxCssByteCountExceeded If the allowed max byte count is exceeded.
     */
    public function setAttribute($name, $value)
    {
        if (
            $name === Attribute::STYLE
            && $this->ownerDocument->isCssMaxByteCountEnforced()
        ) {
            $newByteCount = strlen($value);

            if ($this->ownerDocument->getRemainingCustomCssSpace() < ($newByteCount - $this->inlineStyleByteCount)) {
                throw MaxCssByteCountExceeded::forInlineStyle($this, $value);
            }

            $this->ownerDocument->addInlineStyleByteCount($newByteCount - $this->inlineStyleByteCount);

            $this->inlineStyleByteCount = $newByteCount;
            return parent::setAttribute(Attribute::STYLE, $value);
        }

        return parent::setAttribute($name, $value);
    }

    /**
     * Adds a boolean attribute without value.
     *
     * @param string $name  The name of the attribute.
     * @return DOMAttr|false The new or modified DOMAttr or false if an error occurred.
     * @throws MaxCssByteCountExceeded If the allowed max byte count is exceeded.
     */
    public function addBooleanAttribute($name)
    {
        $attribute = new DOMAttr($name);
        $result    = $this->setAttributeNode($attribute);

        if (!$result instanceof DOMAttr) {
            return false;
        }

        return $result;
    }

    /**
     * Copy one or more attributes from this element to another element.
     *
     * @param array|string $attributes       Attribute name or array of attribute names to copy.
     * @param Element      $target           Target Dom\Element to copy the attributes to.
     * @param string       $defaultSeparator Default separator to use for multiple values if the attribute is not known.
     */
    public function copyAttributes($attributes, Element $target, $defaultSeparator = ',')
    {
        foreach ((array) $attributes as $attribute) {
            if ($this->hasAttribute($attribute)) {
                $values = $this->getAttribute($attribute);
                if ($target->hasAttribute($attribute)) {
                    switch ($attribute) {
                        case Attribute::ON:
                            $values = self::mergeAmpActions($target->getAttribute($attribute), $values);
                            break;
                        case Attribute::CLASS_:
                            $values = $target->getAttribute($attribute) . ' ' . $values;
                            break;
                        default:
                            $values = $target->getAttribute($attribute) . $defaultSeparator . $values;
                    }
                }
                $target->setAttribute($attribute, $values);
            }
        }
    }

    /**
     * Register an AMP action to an event.
     *
     * If the element already contains one or more events or actions, the method
     * will assemble them in a smart way.
     *
     * @param string $event  Event to trigger the action on.
     * @param string $action Action to add.
     */
    public function addAmpAction($event, $action)
    {
        $eventActionString = "{$event}:{$action}";

        if (! $this->hasAttribute(Attribute::ON)) {
            // There's no "on" attribute yet, so just add it and be done.
            $this->setAttribute(Attribute::ON, $eventActionString);
            return;
        }

        $this->setAttribute(
            Attribute::ON,
            self::mergeAmpActions(
                $this->getAttribute(Attribute::ON),
                $eventActionString
            )
        );
    }

    /**
     * Merge two sets of AMP events & actions.
     *
     * @param string $first  First event/action string.
     * @param string $second First event/action string.
     * @return string Merged event/action string.
     */
    public static function mergeAmpActions($first, $second)
    {
        $events = [];
        foreach ([$first, $second] as $eventActionString) {
            $matches = [];
            $results = preg_match_all(self::AMP_EVENT_ACTIONS_REGEX_PATTERN, $eventActionString, $matches);

            if (! $results || ! isset($matches['event'])) {
                continue;
            }

            foreach ($matches['event'] as $index => $event) {
                $events[$event][] = $matches['actions'][ $index ];
            }
        }

        $valueStrings = [];
        foreach ($events as $event => $actionStringsArray) {
            $actionsArray = [];
            array_walk(
                $actionStringsArray,
                static function ($actions) use (&$actionsArray) {
                    $matches = [];
                    $results = preg_match_all(self::AMP_ACTION_REGEX_PATTERN, $actions, $matches);

                    if (! $results || ! isset($matches['action'])) {
                        $actionsArray[] = $actions;
                        return;
                    }

                    $actionsArray = array_merge($actionsArray, $matches['action']);
                }
            );

            $actions         = implode(',', array_unique(array_filter($actionsArray)));
            $valueStrings[] = "{$event}:{$actions}";
        }

        return implode(';', $valueStrings);
    }

    /**
     * Extract this element's HTML attributes and return as an associative array.
     *
     * @return string[] The attributes for the passed node, or an empty array if it has no attributes.
     */
    public function getAttributesAsAssocArray()
    {
        $attributes = [];
        if (! $this->hasAttributes()) {
            return $attributes;
        }

        foreach ($this->attributes as $attribute) {
            $attributes[ $attribute->nodeName ] = $attribute->nodeValue;
        }

        return $attributes;
    }

    /**
     * Add one or more HTML element attributes to this element.
     *
     * @param string[] $attributes One or more attributes for the node's HTML element.
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            try {
                $this->setAttribute($name, $value);
            } catch (MaxCssByteCountExceeded $e) {
                /*
                 * Catch a "Invalid Character Error" when libxml is able to parse attributes with invalid characters,
                 * but it throws error when attempting to set them via DOM methods. For example, '...this' can be parsed
                 * as an attribute but it will throw an exception when attempting to setAttribute().
                 */
                continue;
            }
        }
    }

    /**
     * Magic getter to implement lazily-created, cached properties for the element.
     *
     * @param string $name Name of the property to get.
     * @return mixed Value of the property, or null if unknown property was requested.
     */
    public function __get($name)
    {
        switch ($name) {
            case 'inlineStyleByteCount':
                if (!isset($this->inlineStyleByteCount)) {
                    $this->inlineStyleByteCount = strlen((string)$this->getAttribute(Attribute::STYLE));
                }

                return $this->inlineStyleByteCount;
        }

        // Mimic regular PHP behavior for missing notices.
        trigger_error(self::PROPERTY_GETTER_ERROR_MESSAGE . $name, E_USER_NOTICE);

        return null;
    }
}
