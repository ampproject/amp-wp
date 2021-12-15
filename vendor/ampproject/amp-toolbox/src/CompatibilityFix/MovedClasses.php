<?php

namespace AmpProject\CompatibilityFix;

use AmpProject\CompatibilityFix;

/**
 * Backwards compatibility fix for classes that were moved.
 *
 * @package ampproject/amp-toolbox
 */
final class MovedClasses implements CompatibilityFix
{
    /**
     * Mapping of aliases to be registered.
     *
     * @var array<string, string> Associative array of class alias mappings.
     */
    const ALIASES = [
        // v0.9.0 - moved HTML-based utility into a separate `Html` sub-namespace.
        'AmpProject\AtRule'             => 'AmpProject\Html\AtRule',
        'AmpProject\Attribute'          => 'AmpProject\Html\Attribute',
        'AmpProject\LengthUnit'         => 'AmpProject\Html\LengthUnit',
        'AmpProject\RequestDestination' => 'AmpProject\Html\RequestDestination',
        'AmpProject\Role'               => 'AmpProject\Html\Role',
        'AmpProject\Tag'                => 'AmpProject\Html\Tag',

        // v0.9.0 - extracted `Encoding` out of `Dom\Document`, as it is turned into AMP value object.
        'AmpProject\Dom\Document\Encoding' => 'AmpProject\Encoding',

    ];

    /**
     * Register the compatibility fix.
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(__CLASS__ . '::autoloader');
    }

    /**
     * Autoloader to register.
     *
     * @param string $oldClassName Old class name that was requested to be autoloaded.
     * @return void
     */
    public static function autoloader($oldClassName)
    {
        if (! array_key_exists($oldClassName, self::ALIASES)) {
            return;
        }

        class_alias(self::ALIASES[$oldClassName], $oldClassName, true);
    }
}
