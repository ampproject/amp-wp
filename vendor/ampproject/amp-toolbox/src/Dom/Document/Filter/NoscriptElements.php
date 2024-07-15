<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document;
use AmpProject\Dom\Document\AfterLoadFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;
use AmpProject\Dom\UniqueIdManager;
use AmpProject\Html\Tag;

/**
 * Handle the noscript elements with placeholders.
 *
 * @package ampproject/amp-toolbox
 */
final class NoscriptElements implements BeforeLoadFilter, AfterLoadFilter
{
    /**
     * UniqueIdManager instance to use.
     *
     * @var UniqueIdManager
     */
    private $uniqueIdManager;

    /**
     * NoscriptElements constructor.
     *
     * @param UniqueIdManager $uniqueIdManager UniqueIdManager instance to use.
     */
    public function __construct(UniqueIdManager $uniqueIdManager)
    {
        $this->uniqueIdManager = $uniqueIdManager;
    }

    /**
     * Store the <noscript> markup that was extracted to preserve it during parsing.
     *
     * The array keys are the element IDs for placeholder <meta> tags.
     *
     * @var string[]
     */
    private $noscriptPlaceholderComments = [];

    /**
     * Maybe replace noscript elements with placeholders.
     *
     * This is done because libxml<2.8 might parse them incorrectly.
     * When appearing in the head element, a noscript can cause the head to close prematurely
     * and the noscript gets moved to the body and anything after it which was in the head.
     * See <https://stackoverflow.com/questions/39013102/why-does-noscript-move-into-body-tag-instead-of-head-tag>.
     * This is limited to only running in the head element because this is where the problem lies,
     * and it is important for the AMP_Script_Sanitizer to be able to access the noscript elements
     * in the body otherwise.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     */
    public function beforeLoad($html)
    {
        if (version_compare(LIBXML_DOTTED_VERSION, '2.8', '<')) {
            $result = preg_replace_callback(
                '#^.+?(?=<body)#is',
                function ($headMatches) {
                    return preg_replace_callback(
                        '#<noscript[^>]*>.*?</noscript>#si',
                        function ($noscriptMatches) {
                            $id = $this->uniqueIdManager->getUniqueId('noscript');
                            $this->noscriptPlaceholderComments[$id] = $noscriptMatches[0];
                            return sprintf('<meta class="noscript-placeholder" id="%s">', $id);
                        },
                        $headMatches[0]
                    );
                },
                $html
            );

            if (is_string($result)) {
                $html = $result;
            }
        }

        return $html;
    }

    /**
     * Maybe restore noscript elements with placeholders.
     *
     * This is done because libxml<2.8 might parse them incorrectly.
     * When appearing in the head element, a noscript can cause the head to close prematurely
     * and the noscript gets moved to the body and anything after it which was in the head.
     * See <https://stackoverflow.com/questions/39013102/why-does-noscript-move-into-body-tag-instead-of-head-tag>.
     * This is limited to only running in the head element because this is where the problem lies,
     * and it is important for the AMP_Script_Sanitizer to be able to access the noscript elements
     * in the body otherwise.
     *
     * @param Document $document Document to be processed.
     */
    public function afterLoad(Document $document)
    {
        foreach ($this->noscriptPlaceholderComments as $id => $noscriptHtmlFragment) {
            $placeholderElement = $document->getElementById($id);
            if (!$placeholderElement || !$placeholderElement->parentNode) {
                continue;
            }
            $noscriptFragmentDocument = $document::fromHtmlFragment($noscriptHtmlFragment);
            if (!$noscriptFragmentDocument) {
                continue;
            }
            $exportBody = $noscriptFragmentDocument->getElementsByTagName(Tag::BODY)->item(0);
            if (!$exportBody) {
                continue;
            }

            $importFragment = $document->createDocumentFragment();
            while ($exportBody->firstChild) {
                $importNode = $exportBody->removeChild($exportBody->firstChild);
                $importNode = $document->importNode($importNode, true);
                $importFragment->appendChild($importNode);
            }

            $placeholderElement->parentNode->replaceChild($importFragment, $placeholderElement);
        }
    }
}
