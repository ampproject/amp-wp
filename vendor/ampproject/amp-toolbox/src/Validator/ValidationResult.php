<?php

namespace AmpProject\Validator;

/**
 * Validation result value object.
 *
 * @package ampproject/amp-toolbox
 */
final class ValidationResult
{
    /**
     * Validation result status.
     *
     * @var ValidationStatus
     */
    private $status;

    /**
     * Collection of validation errors that were found.
     *
     * @var ValidationErrorCollection
     */
    private $errors;

    /**
     * Spec rules revision.
     *
     * @var int
     */
    private $specRevision;

    /**
     * Version of the transformer used.
     *
     * If the AMP document is a transformed AMP document, then this is the version of the transformers that were used to
     * transform it. If the document is not transformed, then the value will be 0.
     *
     * @var int
     */
    private $transformerVersion;

    /**
     * The type identifier(s) used on the document.
     *
     * This contains the type identifier of the parsed document, e.g. AMP, AMP4ADS or some other type that has yet to be
     * defined. These are declared on the HTML tag and parsed by the validator engine.
     *
     * @var string[]
     */
    private $typeIdentifiers;

    /**
     * The set of provisions from all matching $attrSpecs in this $tagSpec match.
     *
     * If the $tagSpec is selected, these will be added to the final set of provisions.
     *
     * @var ValueSetProvision[]
     */
    private $valueSetProvisions;

    /**
     * The set of requirements from all matching $attrSpecs in this $tagSpec match.
     *
     * If the $tagSpec is selected, these will be added to the final set of requirements.
     *
     * @var ValueSetRequirement[]
     */
    private $valueSetRequirements;

    /**
     * ValidationResult constructor.
     *
     * @param ValidationStatus          $status               Optional. Validation result status.
     * @param ValidationErrorCollection $errors               Optional. Collection of validation errors that were found.
     * @param int                       $specRevision         Optional. Spec rules revision. Defaults to -1.
     * @param int                       $transformerVersion   Optional. Version of the transformer used. Defaults to 0.
     * @param string[]                  $typeIdentifiers      Optional. The type identifier(s) used on the document.
     *                                                        Defaults to an empty array.
     * @param ValueSetProvision[]       $valueSetProvisions   Optional. The set of provisions from all matching
     *                                                        $attrSpecs in this $tagSpec match. Defaults to an empty
     *                                                        array.
     * @param ValueSetRequirement[]     $valueSetRequirements Optional. The set of requirements from all matching
     *                                                        $attrSpecs in this $tagSpec match. Defaults to an empty
     *                                                        array.
     */
    public function __construct(
        ValidationStatus $status = null,
        ValidationErrorCollection $errors = null,
        $specRevision = -1,
        $transformerVersion = 0,
        $typeIdentifiers = [],
        $valueSetProvisions = [],
        $valueSetRequirements = []
    ) {
        $this->status               = $status instanceof ValidationStatus ? $status : ValidationStatus::UNKNOWN();
        $this->errors               = $errors instanceof ValidationErrorCollection
            ? $errors
            : new ValidationErrorCollection();
        $this->specRevision         = $specRevision;
        $this->transformerVersion   = $transformerVersion;
        $this->typeIdentifiers      = $typeIdentifiers;
        $this->valueSetProvisions   = $valueSetProvisions;
        $this->valueSetRequirements = $valueSetRequirements;
    }

    /**
     * Get the validation status.
     *
     * @return ValidationStatus Current validation status of the validation result.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the validation status.
     *
     * @param ValidationStatus $status New validation status to set the validation result to.
     */
    public function setStatus(ValidationStatus $status)
    {
        $this->status = $status;
    }

    /**
     * Get the validation errors.
     *
     * @return ValidationErrorCollection
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get the spec rules revision.
     *
     * @return int
     */
    public function getSpecRevision()
    {
        return $this->specRevision;
    }

    /**
     * Get the transformer version.
     *
     * @return int
     */
    public function getTransformerVersion()
    {
        return $this->transformerVersion;
    }

    /**
     * Set the transformer version.
     *
     * @param int $transformerVersion Transformer version.
     */
    public function setTransformerVersion($transformerVersion)
    {
        $this->transformerVersion = $transformerVersion;
    }

    /**
     * Get the type identifiers.
     *
     * @return string[]
     */
    public function getTypeIdentifiers()
    {
        return $this->typeIdentifiers;
    }

    /**
     * Add a type identifier.
     *
     * @param string $typeIdentifier Type identifier to add.
     */
    public function addTypeIdentifier($typeIdentifier)
    {
        $this->typeIdentifiers[] = $typeIdentifier;
    }

    /**
     * Get the value set provisions.
     *
     * @return ValueSetProvision[]
     */
    public function getValueSetProvisions()
    {
        return $this->valueSetProvisions;
    }

    /**
     * Get the value set requirements.
     *
     * @return ValueSetRequirement[]
     */
    public function getValueSetRequirements()
    {
        return $this->valueSetRequirements;
    }
}
