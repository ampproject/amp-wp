<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator;

final class Spec
{
    /** @var Spec\Section\Tags */
    private $tags;

    /** @var int */
    private $minValidatorRevisionRequired = 475;

    /** @var int */
    private $specFileRevision = 1188;

    /** @var string */
    private $templateSpecUrl = 'https://amp.dev/documentation/components/amp-mustache';

    /** @var string */
    private $stylesSpecUrl = 'https://amp.dev/documentation/guides-and-tutorials/develop/style_and_layout/style_pages/';

    /** @var string */
    private $scriptSpecUrl = 'https://amp.dev/documentation/guides-and-tutorials/learn/validation-workflow/validation_errors/#custom-javascript-is-not-allowed';

    /** @var Spec\Section\CssRulesets */
    private $cssRulesets;

    /** @var Spec\Section\DocRulesets */
    private $docRulesets;

    /** @var Spec\Section\AttributeLists */
    private $attributeLists;

    /** @var Spec\Section\DeclarationLists */
    private $declarationLists;

    /** @var Spec\Section\DescendantTagLists */
    private $descendantTagLists;

    /** @var Spec\Section\Errors */
    private $errors;

    /**
     * @return Spec\Section\Tags
     */
    public function tags()
    {
        if ($this->tags === null) {
            $this->tags = new Spec\Section\Tags();
        }
        return $this->tags;
    }

    /**
     * @return int
     */
    public function minValidatorRevisionRequired()
    {
        return $this->minValidatorRevisionRequired;
    }

    /**
     * @return int
     */
    public function specFileRevision()
    {
        return $this->specFileRevision;
    }

    /**
     * @return string
     */
    public function templateSpecUrl()
    {
        return $this->templateSpecUrl;
    }

    /**
     * @return string
     */
    public function stylesSpecUrl()
    {
        return $this->stylesSpecUrl;
    }

    /**
     * @return string
     */
    public function scriptSpecUrl()
    {
        return $this->scriptSpecUrl;
    }

    /**
     * @return Spec\Section\CssRulesets
     */
    public function cssRulesets()
    {
        if ($this->cssRulesets === null) {
            $this->cssRulesets = new Spec\Section\CssRulesets();
        }
        return $this->cssRulesets;
    }

    /**
     * @return Spec\Section\DocRulesets
     */
    public function docRulesets()
    {
        if ($this->docRulesets === null) {
            $this->docRulesets = new Spec\Section\DocRulesets();
        }
        return $this->docRulesets;
    }

    /**
     * @return Spec\Section\AttributeLists
     */
    public function attributeLists()
    {
        if ($this->attributeLists === null) {
            $this->attributeLists = new Spec\Section\AttributeLists();
        }
        return $this->attributeLists;
    }

    /**
     * @return Spec\Section\DeclarationLists
     */
    public function declarationLists()
    {
        if ($this->declarationLists === null) {
            $this->declarationLists = new Spec\Section\DeclarationLists();
        }
        return $this->declarationLists;
    }

    /**
     * @return Spec\Section\DescendantTagLists
     */
    public function descendantTagLists()
    {
        if ($this->descendantTagLists === null) {
            $this->descendantTagLists = new Spec\Section\DescendantTagLists();
        }
        return $this->descendantTagLists;
    }

    /**
     * @return Spec\Section\Errors
     */
    public function errors()
    {
        if ($this->errors === null) {
            $this->errors = new Spec\Section\Errors();
        }
        return $this->errors;
    }
}
