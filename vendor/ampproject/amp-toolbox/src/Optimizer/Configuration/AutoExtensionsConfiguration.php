<?php

namespace AmpProject\Optimizer\Configuration;

use AmpProject\Amp;
use AmpProject\Exception\InvalidExtension;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Optimizer\Exception\InvalidConfigurationValue;
use ReflectionClass;

/**
 * Configuration for the AutoExtensions transformer.
 *
 * @property string $format                  Specifies the AMP format. Defaults to `AMP`.
 * @property bool   $autoExtensionImport     Set to `false` to disable the auto extension import. Defaults to `true`.
 * @property bool   $experimentBindAttribute Enables experimental conversion of bind attributes. Defaults to `false`.
 *
 * @package ampproject/amp-toolbox
 */
final class AutoExtensionsConfiguration extends BaseTransformerConfiguration
{
    /**
     * Configuration key that specifies the AMP format.
     *
     * @var string
     */
    const FORMAT = 'format';

    /**
     * Configuration key that can disable the automatic importing of extension.
     *
     * @var string
     */
    const AUTO_EXTENSION_IMPORT = 'autoExtensionImport';

    /**
     * Configuration key that enables experimental conversion of bind attributes.
     *
     * @var string
     */
    const EXPERIMENT_BIND_ATTRIBUTE = 'experimentBindAttribute';

    /**
     * Configuration key that allows individual configuration of extension versions.
     *
     * @var string
     */
    const EXTENSION_VERSIONS = 'extensionVersions';

    /**
     * An array of extension names that will not auto import.
     *
     * @var string
     */
    const IGNORED_EXTENSIONS = 'ignoredExtensions';

    /**
     * An array of extension names that will not auto import.
     *
     * @var string
     */
    const REMOVE_UNNEEDED_EXTENSIONS = 'removeUnneededExtensions';

    /**
     * Get the associative array of allowed keys and their respective default values.
     *
     * The array index is the key and the array value is the key's default value.
     *
     * @return array Associative array of allowed keys and their respective default values.
     */
    protected function getAllowedKeys()
    {
        return [
            self::FORMAT                     => Format::AMP,
            self::AUTO_EXTENSION_IMPORT      => true,
            self::EXPERIMENT_BIND_ATTRIBUTE  => false,
            self::EXTENSION_VERSIONS         => [],
            self::IGNORED_EXTENSIONS         => [],
            self::REMOVE_UNNEEDED_EXTENSIONS => false,
        ];
    }

    /**
     * Validate an individual configuration entry.
     *
     * @param string $key   Key of the configuration entry to validate.
     * @param mixed  $value Value of the configuration entry to validate.
     * @return mixed Validated value.
     */
    protected function validate($key, $value)
    {
        switch ($key) {
            case self::FORMAT:
                if (! is_string($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::FORMAT,
                        'string',
                        gettype($value)
                    );
                }

                if (! in_array($value, Amp::FORMATS, true)) {
                    throw InvalidConfigurationValue::forUnknownSubValue(
                        self::class,
                        self::FORMAT,
                        Amp::FORMATS,
                        $value
                    );
                }
                break;

            case self::AUTO_EXTENSION_IMPORT:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::AUTO_EXTENSION_IMPORT,
                        'boolean',
                        gettype($value)
                    );
                }
                break;

            case self::EXPERIMENT_BIND_ATTRIBUTE:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::EXPERIMENT_BIND_ATTRIBUTE,
                        'boolean',
                        gettype($value)
                    );
                }
                break;

            case self::EXTENSION_VERSIONS:
                if (! is_array($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::EXTENSION_VERSIONS,
                        'array',
                        gettype($value)
                    );
                }
                break;

            case self::IGNORED_EXTENSIONS:
                if (! is_array($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::IGNORED_EXTENSIONS,
                        'array',
                        gettype($value)
                    );
                }

                // Assert that the extension names in the ignore list are valid extensions.
                $reflection = new ReflectionClass(Extension::class);
                $constants = $reflection->getConstants();

                foreach ($value as $extension) {
                    if (! in_array($extension, $constants, true)) {
                        throw InvalidExtension::forExtension($extension);
                    }
                }
                break;

            case self::REMOVE_UNNEEDED_EXTENSIONS:
                if (! is_bool($value)) {
                    throw InvalidConfigurationValue::forInvalidSubValueType(
                        self::class,
                        self::REMOVE_UNNEEDED_EXTENSIONS,
                        'boolean',
                        gettype($value)
                    );
                }
                break;
        }

        return $value;
    }
}
