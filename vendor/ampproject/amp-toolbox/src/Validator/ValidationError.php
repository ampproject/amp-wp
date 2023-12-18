<?php

namespace AmpProject\Validator;

/**
 * Validation error value object.
 *
 * @package ampproject/amp-toolbox
 */
final class ValidationError
{
    /**
     * Severity of the validation error.
     *
     * @var ValidationSeverity
     */
    private $severity;

    /**
     * Code of the validation error.
     *
     * @var int
     */
    private $code;

    /**
     * Line that the validation error was found in.
     *
     * @var int
     */
    private $line;

    /**
     * Column that the validation error was found in.
     *
     * @var int|null
     */
    private $column;

    /**
     * Spec URL related to the validation error.
     *
     * @var string|null
     */
    private $specUrl;

    /**
     * Array of additional parameters for the validation error.
     *
     * @var string[]
     */
    private $params;

    /**
     * ValidationError constructor.
     *
     * @param ValidationSeverity $severity Severity of the validation error.
     * @param int                $code     Code of the validation error.
     * @param int                $line     Optional. Line that the validation error was found in. Defaults to 1.
     * @param int|null           $column   Optional. Column that the validation error was found in.
     * @param string|null        $specUrl  Optional. Spec URL related to the validation error.
     * @param array<string>|null $params   Optional. Array of additional parameters for the validation error.
     */
    public function __construct(
        ValidationSeverity $severity,
        $code,
        $line = 1,
        $column = null,
        $specUrl = null,
        $params = []
    ) {
        $this->severity = $severity;
        $this->code     = $code;
        $this->line     = $line;
        $this->column   = $column;
        $this->specUrl  = $specUrl;
        $this->params   = $params;
    }

    /**
     * Get the severity of the validation error.
     *
     * @return ValidationSeverity Severity of the validation error.
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Get the code of the validation error.
     *
     * @return int Code of the validation error.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the line of the validation error.
     *
     * @return int Line of the validation error.
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Get the column of the validation error.
     *
     * @return int|null Column of the validation error.
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Get the specification URL of the validation error.
     *
     * @return string|null Specification URL of the validation error.
     */
    public function getSpecUrl()
    {
        return $this->specUrl;
    }

    /**
     * Get the params of the validation error.
     *
     * @return array Params of the validation error.
     */
    public function getParams()
    {
        return $this->params;
    }
}
