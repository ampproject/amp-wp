<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document\AfterSaveFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;

/**
 * Filter to secure and restore the doctype node.
 *
 * @package ampproject/amp-toolbox
 */
final class DoctypeNode implements BeforeLoadFilter, AfterSaveFilter
{
    /**
     * Regex pattern used for securing the doctype node if it is not the first one.
     *
     * @var string
     */
    const HTML_SECURE_DOCTYPE_IF_NOT_FIRST_PATTERN = '/(^[^<]*(?>\s*<!--[^>]*>\s*)+<)(!)(doctype)(\s+[^>]+?)(>)/i';

    /**
     * Regex replacement template for securing the doctype node.
     *
     * @var string.
     */
    const HTML_SECURE_DOCTYPE_REPLACEMENT_TEMPLATE = '\1!--amp-\3\4-->';

    /**
     * Regex pattern used for restoring the doctype node.
     *
     * @var string
     */
    const HTML_RESTORE_DOCTYPE_PATTERN = '/(^[^<]*(?>\s*<!--[^>]*>\s*)*<)(!--amp-)(doctype)(\s+[^>]+?)(-->)/i';

    /**
     * Regex replacement template for restoring the doctype node.
     *
     * @var string
     */
    const HTML_RESTORE_DOCTYPE_REPLACEMENT_TEMPLATE = '\1!\3\4>';

    /**
     * Whether we had secured a doctype that needs restoring or not.
     *
     * This is an int as it receives the $count from the preg_replace().
     *
     * @var int
     */
    private $securedDoctype = 0;

    /**
     * Secure the original doctype node.
     *
     * We need to keep elements around that were prepended to the doctype, like comment node used for source-tracking.
     * As DOM_Document prepends a new doctype node and removes the old one if the first element is not the doctype, we
     * need to ensure the original one is not stripped (by changing its node type) and restore it later on.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     */
    public function beforeLoad($html)
    {
        $result = preg_replace(
            self::HTML_SECURE_DOCTYPE_IF_NOT_FIRST_PATTERN,
            self::HTML_SECURE_DOCTYPE_REPLACEMENT_TEMPLATE,
            $html,
            1,
            $this->securedDoctype
        );

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }

    /**
     * Restore the original doctype node.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     */
    public function afterSave($html)
    {
        if (! $this->securedDoctype) {
            return $html;
        }

        $result = preg_replace(
            self::HTML_RESTORE_DOCTYPE_PATTERN,
            self::HTML_RESTORE_DOCTYPE_REPLACEMENT_TEMPLATE,
            $html,
            1
        );

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }
}
