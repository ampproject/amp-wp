<?php

namespace AmpProject\Dom\Document;

/**
 * Filter the HTML after it is saved from the Dom\Document.
 *
 * @package ampproject/amp-toolbox
 */
interface AfterSaveFilter extends Filter
{
    /**
     * Process the Dom\Document after being saved from Dom\Document.
     *
     * @param string $html String of HTML markup to be preprocessed.
     * @return string Preprocessed string of HTML markup.
     */
    public function afterSave($html);
}
