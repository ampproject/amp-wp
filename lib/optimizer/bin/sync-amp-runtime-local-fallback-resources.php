#!/usr/bin/env php
<?php

use AmpProject\Optimizer\LocalFallback;

$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    include $autoloader;
}

function ensureDirExists($directory)
{
    $parent = dirname($directory);

    if (! empty($parent) && ! is_dir($parent)) {
        ensureDirExists($parent);
    }

    if (! is_dir($directory) && ! mkdir($directory) && ! is_dir($directory)) {
        abort("Couldn't create directory '{$directory}'.");
    }
}

ensureDirExists(LocalFallback::ROOT_FOLDER);

foreach (LocalFallback::getMappings() as $url => $filepath) {
    ensureDirExists(dirname($filepath));
    echo "Downloading '{$url}' ... ";
    file_put_contents($filepath, file_get_contents($url));
    echo "OK.\n";
}
