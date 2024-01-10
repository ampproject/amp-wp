<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document;
use AmpProject\Dom\Document\AfterLoadFilter;
use AmpProject\Dom\Element;
use AmpProject\Html\Tag;
use DOMAttr;

/**
 * Filter for deduplicating head and body tags.
 *
 * @package ampproject/amp-toolbox
 */
final class DeduplicateTag implements AfterLoadFilter
{
    /**
     * Deduplicate head and body tags.
     *
     * This keeps the first tag as the main tag and moves over all child nodes and attribute nodes from any subsequent
     * same tags over to remove them.
     *
     * @param Document $document Document to be processed.
     */
    public function afterLoad(Document $document)
    {
        $tagNames = [Tag::HEAD, Tag::BODY];

        foreach ($tagNames as $tagName) {
            $tags = $document->getElementsByTagName($tagName);

            /**
             * Main tag to keep.
             *
             * @var Element|null $mainTag
             */
            $mainTag = $tags->item(0);

            if (null === $mainTag) {
                continue;
            }

            while ($tags->length > 1) {
                /**
                 * Tag to remove.
                 *
                 * @var Element $tagToRemove
                 */
                $tagToRemove = $tags->item(1);

                foreach ($tagToRemove->childNodes as $childNode) {
                    $mainTag->appendChild($childNode->parentNode->removeChild($childNode));
                }

                while ($tagToRemove->hasAttributes()) {
                    /**
                     * Attribute node to move over to the main tag.
                     *
                     * @var DOMAttr $attribute
                     */
                    $attribute = $tagToRemove->attributes->item(0);
                    $tagToRemove->removeAttributeNode($attribute);

                    // @TODO This doesn't deal properly with attributes present on both tags. Maybe overkill to add?
                    // We could move over the copy_attributes from AMP_DOM_Utils to do this.
                    $mainTag->setAttributeNode($attribute);
                }

                $tagToRemove->parentNode->removeChild($tagToRemove);
            }

            // Avoid doing the above query again if possible.
            if (in_array($tagName, [Tag::HEAD, Tag::BODY], true)) {
                $document->$tagName = $mainTag;
            }
        }
    }
}
