<?php

namespace AmpProject\Validator;

use AmpProject\Html\Parser\DocLocator;
use AmpProject\Html\Parser\ParsedAttribute;

/**
 * The Context keeps track of the line / column that the validator is in, as well as the mandatory tag specs that have
 * already been validated. So, this constitutes the mutable state for the validator except for the validation result
 * itself.
 *
 * @package ampproject/amp-toolbox
 */
final class Context
{
    /**
     * Validator rules to be applied.
     *
     * @var ValidatorRules
     */
    private $rules;

    /**
     * The mandatory alternatives that we've validated (a small list of ids).
     *
     * @var array<int>
     */
    private $mandatoryAlternativesSatisfied = [];

    /**
     * Set of type identifiers in this document.
     *
     * @var array<string>
     */
    private $typeIdentifiers = [];

    /**
     * DocLocator object from the parser which gives us line / column numbers.
     *
     * @var DocLocator
     */
    private $locator;

    /**
     * Attributes that were found in the (last) <body> tag.
     *
     * @var ParsedAttribute[]|null
     */
    private $encounteredBodyAttributes;

    /**
     * Position in the file of the (last) <body> tag.
     *
     * @var FilePosition
     */
    private $encounteredBodyFilePosition;

    /**
     * Context keeping track of the extensions.
     *
     * @var ExtensionsContext
     */
    private $extensionsContext;

    /**
     * Instantiate a Context object.
     *
     * @param ValidatorRules $rules Validator rules to be applied.
     */
    public function __construct(ValidatorRules $rules)
    {
        $this->rules             = $rules;
        $this->extensionsContext = new ExtensionsContext();
    }

    /**
     * Set the document locator to be used.
     *
     * @param DocLocator $locator DocLocator instance to use.
     */
    public function setDocLocator(DocLocator $locator)
    {
        $this->locator = $locator;
    }


    /**
     * Get the validation rules to be applied.
     *
     * @return ValidatorRules Validation rules.
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Get the line and column positions into the file.
     *
     * @return FilePosition
     */
    public function getFilePosition()
    {
        return new FilePosition($this->locator->getLine(), $this->locator->getColumn());
    }

    /**
     * Add an error field to validationResult with severity WARNING.
     *
     * @param int              $validationErrorCode Error code.
     * @param FilePosition     $filePosition        Position into the file as a line / column pair.
     * @param array            $params              Additional params to store with the error.
     * @param string           $specUrl             A link (URL) to the amphtml spec.
     * @param ValidationResult $validationResult    Validation result to add the warning to.
     */
    public function addWarning($validationErrorCode, FilePosition $filePosition, $params, $specUrl, $validationResult)
    {
        $this->recordError(
            new ValidationError(
                ValidationSeverity::WARNING(),
                $validationErrorCode,
                $filePosition->getLine(),
                $filePosition->getColumn(),
                $specUrl,
                $params
            ),
            $validationResult
        );
    }

    /**
     * Add an error field to validationResult with severity ERROR.
     *
     * @param int              $validationErrorCode Error code.
     * @param FilePosition     $filePosition        Position into the file as a line / column pair.
     * @param array            $params              Additional params to store with the error.
     * @param string           $specUrl             A link (URL) to the amphtml spec.
     * @param ValidationResult $validationResult    Validation result to add the warning to.
     */
    public function addError($validationErrorCode, FilePosition $filePosition, $params, $specUrl, $validationResult)
    {
        $this->recordError(
            new ValidationError(
                ValidationSeverity::ERROR(),
                $validationErrorCode,
                $filePosition->getLine(),
                $filePosition->getColumn(),
                $specUrl,
                $params
            ),
            $validationResult
        );
    }

    /**
     * Record an encountered <body> tag.
     *
     * @param ParsedAttribute[] $attributes Attributes of the body tag.
     */
    public function recordBodyTag($attributes)
    {
        $this->encounteredBodyAttributes   = $attributes;
        $this->encounteredBodyFilePosition = $this->getFilePosition();
    }

    /**
     * Record an error and check if the status needs to be adapted.
     *
     * @param ValidationError  $error            Error to record.
     * @param ValidationResult $validationResult Validation result to adapt.
     */
    public function recordError(ValidationError $error, ValidationResult $validationResult)
    {
        // If any of the errors amount to more than a WARNING, validation fails.
        if (! $error->getSeverity()->equals(ValidationSeverity::WARNING())) {
            $validationResult->setStatus(ValidationStatus::FAIL());
        }

        $validationResult->getErrors()->add($error);
    }

    /**
     * Get the extensions context.
     *
     * @return ExtensionsContext Extensions context.
     */
    public function getExtensionsContext()
    {
        return $this->extensionsContext;
    }

    /**
     * Get the set of encountered <body> tag attributes.
     *
     * If no <body> tag has been encountered yet, this returns null instead.
     *
     * @return ParsedAttribute[]|null Array of attributes, or null if no <body> tag was encountered yet.
     */
    public function getEncounteredBodyAttributes()
    {
        return $this->encounteredBodyAttributes;
    }

    /**
     * Get the position within the file of the encountered <body> tag.
     *
     * @return FilePosition Position within the file of the encountered <body> tag.
     */
    public function getEncounteredBodyFilePosition()
    {
        return $this->encounteredBodyFilePosition;
    }

    /**
     * Record a newly encountered type identifier.
     *
     * @param string $typeIdentifier Type identifier to record.
     */
    public function recordTypeIdentifier($typeIdentifier)
    {
        $this->typeIdentifiers[] = $typeIdentifier;
    }
}
