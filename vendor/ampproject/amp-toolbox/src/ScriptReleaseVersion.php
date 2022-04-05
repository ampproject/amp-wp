<?php

namespace AmpProject;

/**
 * Enum for denoting script release versions in use by AMP.
 *
 * @method static ScriptReleaseVersion UNKNOWN()
 * @method static ScriptReleaseVersion STANDARD()
 * @method static ScriptReleaseVersion LTS()
 * @method static ScriptReleaseVersion MODULE_NOMODULE()
 * @method static ScriptReleaseVersion MODULE_NOMODULE_LTS()
 *
 * @package ampproject/amp-toolbox
 */
final class ScriptReleaseVersion extends FakeEnum
{
    const UNKNOWN             = 'unknown';
    const STANDARD            = 'standard';
    const LTS                 = 'lts';
    const MODULE_NOMODULE     = 'module/nomodule';
    const MODULE_NOMODULE_LTS = 'module/nomodule LTS';
}
