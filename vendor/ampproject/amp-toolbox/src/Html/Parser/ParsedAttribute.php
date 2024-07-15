<?php

namespace AmpProject\Html\Parser;

/**
 * Name/Value pair representing an HTML Tag attribute.
 *
 * @package ampproject/amp-toolbox
 */
final class ParsedAttribute
{
    /**
     * Name of the attribute.
     *
     * @var string
     */
    private $name;

    /**
     * Value of the attribute.
     *
     * @var string
     */
    private $value;

    /**
     * ParsedAttribute constructor.
     *
     * @param string $name  Name of the attribute.
     * @param string $value Value of the attribute.
     */
    public function __construct($name, $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    /**
     * Get the name of the attribute.
     *
     * @return string Name of the attribute.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the value of the attribute.
     *
     * @return string Value of the attribute.
     */
    public function value()
    {
        return $this->value;
    }
}
