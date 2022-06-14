<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Amp;
use AmpProject\Dom\Document\AfterSaveFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;
use AmpProject\Dom\Document\Option;
use AmpProject\Dom\Options;

/**
 * Amp bind attributes filter.
 *
 * @package ampproject/amp-toolbox
 */
final class AmpBindAttributes implements BeforeLoadFilter, AfterSaveFilter
{
    /**
     * Pattern for HTML attribute accounting for binding attr name in data attribute syntax, boolean attribute,
     * single/double-quoted attribute value, and unquoted attribute values.
     *
     * @var string
     */
    const AMP_BIND_DATA_ATTRIBUTE_ATTR_PATTERN = '#^\s+(?P<name>(?:'
                                                 . Amp::BIND_DATA_ATTR_PREFIX
                                                 . ')?[a-zA-Z0-9_\-]+)'
                                                 . '(?P<value>=(?>"[^"]*+"|\'[^\']*+\'|[^\'"\s]+))?#';
    /**
     * Match all start tags that contain a binding attribute in data attribute syntax.
     *
     * @var string
     */
    const AMP_BIND_DATA_START_PATTERN = '#<'
                                        . '(?P<name>[a-zA-Z0-9_\-]+)'               // Tag name.
                                        . '(?P<attrs>\s+'                           // Attributes.
                                        . '(?>'                                 // Match at least one attribute.
                                        . '(?>'                             // prefixed with "data-amp-bind-".
                                        . '(?![a-zA-Z0-9_\-\s]*'
                                        . Amp::BIND_DATA_ATTR_PREFIX
                                        . '[a-zA-Z0-9_\-]+="[^"]*+"|\'[^\']*+\')'
                                        . '[^>"\']+|"[^"]*+"|\'[^\']*+\''
                                        . ')*+'
                                        . '(?>[a-zA-Z0-9_\-\s]*'
                                        . Amp::BIND_DATA_ATTR_PREFIX
                                        . '[a-zA-Z0-9_\-]+'
                                        . ')'
                                        . ')+'
                                        . '(?>[^>"\']+|"[^"]*+"|\'[^\']*+\')*+' // Any attribute tokens, including
                                        // binding ones.
                                        . ')>#is';

    /**
     * Pattern for HTML attribute accounting for binding attr name in square brackets syntax, boolean attribute,
     * single/double-quoted attribute value, and unquoted attribute values.
     *
     * @var string
     */
    const AMP_BIND_SQUARE_BRACKETS_ATTR_PATTERN = '#^\s+(?P<name>\[?[a-zA-Z0-9_\-]+\]?)'
                                                  . '(?P<value>=(?>"[^"]*+"|\'[^\']*+\'|[^\'"\s]+))?#';
    /**
     * Match all start tags that contain a binding attribute in square brackets syntax.
     *
     * @var string
     */
    const AMP_BIND_SQUARE_START_PATTERN = '#<'
                                          . '(?P<name>[a-zA-Z0-9_\-]+)'               // Tag name.
                                          . '(?P<attrs>\s+'                           // Attributes.
                                          . '(?>[^>"\'\[\]]+|"[^"]*+"|\'[^\']*+\')*+' // Non-binding attributes tokens.
                                          . '\[[a-zA-Z0-9_\-]+\]'                     // One binding attribute key.
                                          . '(?>[^>"\']+|"[^"]*+"|\'[^\']*+\')*+'     // Any attribute tokens, including
                                          // binding ones.
                                          . ')>#s';

    /**
     * Options instance to use.
     *
     * @var Options
     */
    private $options;

    /**
     * Store the names of the amp-bind attributes that were converted so that we can restore them later on.
     *
     * @var array<string>
     */
    private $convertedAmpBindAttributes = [];

    /**
     * AmpBindAttributes constructor.
     *
     * @param Options $options Options instance to use.
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * Replace AMP binding attributes with something that libxml can parse (as HTML5 data-* attributes).
     *
     * This is necessary because attributes in square brackets are not understood in PHP and
     * get dropped with an error raised:
     * > Warning: DOMDocument::loadHTML(): error parsing attribute name
     *
     * @link https://www.ampproject.org/docs/reference/components/amp-bind
     *
     * @param string $html HTML containing amp-bind attributes.
     * @return string HTML with AMP binding attributes replaced with HTML5 data-* attributes.
     */
    public function beforeLoad($html)
    {
        /**
         * Replace callback.
         *
         * @param array $tagMatches Tag matches.
         * @return string Replacement.
         */
        $replaceCallback = function ($tagMatches) {
            $oldAttrs = $this->maybeStripSelfClosingSlash($tagMatches['attrs']);
            $newAttrs = '';
            $offset   = 0;
            while (
                preg_match(
                    self::AMP_BIND_SQUARE_BRACKETS_ATTR_PATTERN,
                    substr($oldAttrs, $offset),
                    $attrMatches
                )
            ) {
                $offset += strlen($attrMatches[0]);

                if ('[' === $attrMatches['name'][0]) {
                    $attrName = trim($attrMatches['name'], '[]');
                    $newAttrs .= ' ' . Amp::BIND_DATA_ATTR_PREFIX . $attrName;
                    if (isset($attrMatches['value'])) {
                        $newAttrs .= $attrMatches['value'];
                    }
                    $this->convertedAmpBindAttributes[] = $attrName;
                } else {
                    $newAttrs .= $attrMatches[0];
                }
            }

            // Bail on parse error which occurs when the regex isn't able to consume the entire $newAttrs string.
            if (strlen($oldAttrs) !== $offset) {
                return $tagMatches[0];
            }

            return '<' . $tagMatches['name'] . $newAttrs . '>';
        };

        $result = preg_replace_callback(
            self::AMP_BIND_SQUARE_START_PATTERN,
            $replaceCallback,
            $html
        );

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }

    /**
     * Convert AMP bind-attributes back to their original syntax.
     *
     * This is not guaranteed to produce the exact same result as the initial markup, as it is more of a best guess.
     * It can end up replacing the wrong attributes if the initial markup had inconsistent styling, mixing both syntaxes
     * for the same attribute. In either case, it will always produce working markup, so this is not that big of a deal.
     *
     * @see convertAmpBindAttributes() Reciprocal function.
     * @link https://www.ampproject.org/docs/reference/components/amp-bind
     *
     * @param string $html HTML with amp-bind attributes converted.
     * @return string HTML with amp-bind attributes restored.
     */
    public function afterSave($html)
    {
        if ($this->options[Option::AMP_BIND_SYNTAX] === Option::AMP_BIND_SYNTAX_DATA_ATTRIBUTE) {
            // All amp-bind attributes should remain in their converted data attribute form.
            return $html;
        }

        if (
            $this->options[Option::AMP_BIND_SYNTAX] === Option::AMP_BIND_SYNTAX_AUTO
            &&
            empty($this->convertedAmpBindAttributes)
        ) {
            // Only previously converted amp-bind attributes should be restored, but none were converted.
            return $html;
        }

        /**
         * Replace callback.
         *
         * @param array $tagMatches Tag matches.
         * @return string Replacement.
         */
        $replaceCallback = function ($tagMatches) {
            $oldAttrs = $this->maybeStripSelfClosingSlash($tagMatches['attrs']);
            $newAttrs = '';
            $offset   = 0;
            while (
                preg_match(
                    self::AMP_BIND_DATA_ATTRIBUTE_ATTR_PATTERN,
                    substr($oldAttrs, $offset),
                    $attrMatches
                )
            ) {
                $offset += strlen($attrMatches[0]);

                $attrName = substr($attrMatches['name'], strlen(Amp::BIND_DATA_ATTR_PREFIX));
                if (
                    $this->options[Option::AMP_BIND_SYNTAX] === Option::AMP_BIND_SYNTAX_SQUARE_BRACKETS
                    ||
                    in_array($attrName, $this->convertedAmpBindAttributes, true)
                ) {
                    $attrValue = isset($attrMatches['value']) ? $attrMatches['value'] : '=""';
                    $newAttrs .= " [{$attrName}]{$attrValue}";
                } else {
                    $newAttrs .= $attrMatches[0];
                }
            }

            // Bail on parse error which occurs when the regex isn't able to consume the entire $newAttrs string.
            if (strlen($oldAttrs) !== $offset) {
                return $tagMatches[0];
            }

            return '<' . $tagMatches['name'] . $newAttrs . '>';
        };

        $result = preg_replace_callback(
            self::AMP_BIND_DATA_START_PATTERN,
            $replaceCallback,
            $html
        );

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }

    /**
     * Strip the self-closing slash as long as it is not an attribute value, like for the href attribute.
     *
     * @param string $attributes Attributes to strip the self-closing slash of.
     * @return string Adapted attributes.
     */
    private function maybeStripSelfClosingSlash($attributes)
    {
        $result = preg_replace('#(?<!=)/$#', '', $attributes);

        if (! is_string($result)) {
            return rtrim($attributes);
        }

        return rtrim($result);
    }
}
