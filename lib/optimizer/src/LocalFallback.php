<?php

namespace AmpProject\Optimizer;

use AmpProject\Amp;

final class LocalFallback
{

    /**
     * Domain for which the mapped files will have a local fallback.
     */
    const MAPPED_DOMAIN = Amp::CACHE_HOST;

    /**
     * Root folder where the local fallback files are stored.
     *
     * @var string
     */
    const ROOT_FOLDER = __DIR__ . '/../resources/local_fallback';

    /**
     * Array of mapped files for which a local fallback is provided.
     *
     * @var string[]
     */
    const MAPPED_FILES = [
        'rtv/metadata',
        'v0.css',
    ];

    /**
     * Get the mappings that are provided as local fallbacks.
     *
     * @return array Associative array of mappings mapping a URL to a filepath.
     */
    public static function getMappings()
    {
        static $mappings = null;

        if ($mappings === null) {
            $rootFolder = realpath(self::ROOT_FOLDER);
            foreach (self::MAPPED_FILES as $mappedFile) {
                $mappings[self::MAPPED_DOMAIN . '/' . $mappedFile] = "{$rootFolder}/{$mappedFile}";
            }
        }

        return $mappings;
    }
}
