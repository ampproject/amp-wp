<?php

namespace AmpProject\Validator;

/**
 * Return type tuple for `ValidationHandler::validateTag()`.
 *
 * @package ampproject/amp-toolbox
 */
final class ValidateTagResult
{
    /**
     * Validation result.
     *
     * @var ValidationResult
     */
    private $validationResult;

    /**
     * Tag spec that was the best match.
     *
     * @var ParsedTagSpec|null
     */
    private $bestMatchTagSpec;

    /**
     * Count of bytes of inline CSS styles.
     *
     * @var int
     */
    private $inlineStyleCssBytes;

    /**
     * Developer mode suppression.
     *
     * @var bool|null
     */
    private $devModeSuppress;

    /**
     * Instantiate a ValidateTagResult object.
     *
     * @param ValidationResult   $validationResult    Validation result.
     * @param ParsedTagSpec|null $bestMatchTagSpec    Tag spec that was the best match.
     * @param int                $inlineStyleCssBytes Count of bytes of inline CSS styles.
     * @param null               $devModeSuppress     Developer mode suppression.
     */
    public function __construct(
        ValidationResult $validationResult,
        ParsedTagSpec $bestMatchTagSpec = null,
        $inlineStyleCssBytes = 0,
        $devModeSuppress = null
    ) {
        $this->validationResult = $validationResult;
        $this->bestMatchTagSpec = $bestMatchTagSpec;
        $this->inlineStyleCssBytes = $inlineStyleCssBytes;
        $this->devModeSuppress = $devModeSuppress;
    }

    /**
     * Get the validation result.
     *
     * @return ValidationResult
     */
    public function getValidationResult()
    {
        return $this->validationResult;
    }

    /**
     * Get the tag spec that was the best match.
     *
     * @return ParsedTagSpec|null
     */
    public function getBestMatchTagSpec()
    {
        return $this->bestMatchTagSpec;
    }

    /**
     * Get the count of bytes of inline CSS styles.
     *
     * @return int
     */
    public function getInlineStyleCssBytes()
    {
        return $this->inlineStyleCssBytes;
    }

    /**
     * Get the state of the developer mode suppression.
     *
     * @return bool|null
     */
    public function getDevModeSuppress()
    {
        return $this->devModeSuppress;
    }
}
