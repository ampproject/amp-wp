<?php

namespace AmpProject;

/**
 * @var array<class-string<CompatibilityFix>> $compatibilityFixes
 */
$compatibilityFixes = [
    CompatibilityFix\MovedClasses::class,
];

foreach ($compatibilityFixes as $compatibilityFix) {
    $compatibilityFix::register();
}
