#!/usr/bin/env php
<?php

$sourceUrl       = 'https://github.com/ampproject/amp-toolbox/archive/master.zip';
$targetFolder    = realpath(dirname(__DIR__) . '/tests') . '/spec';
$tmpFilePath     = tempnam(sys_get_temp_dir(), 'amp-optimizer-');
$tmpFile         = null;
$zip             = null;
$targetSubFolder = 'amp-toolbox-master/packages/optimizer/spec/';

function cleanUp()
{
    global $tmpFilePath, $tmpFile, $zip;

    if ($zip !== null) {
        $zip->close();
    }

    if ($tmpFile !== null) {
        flock($tmpFile, LOCK_UN);
        fclose($tmpFile);
    }

    if (file_exists($tmpFilePath)) {
        unlink($tmpFilePath);
    }
}

function abort($message, $status = 1)
{
    cleanUp();

    fwrite(STDERR, "Error: {$message}\n");

    exit($status);
}

function ensureDirExists($directory)
{
    if (! is_dir($directory) && ! mkdir($directory) && ! is_dir($directory)) {
        abort("Couldn't create directory '{$directory}'.");
    }
}

function filterSpecFile($targetName, $contents) {
    if ( substr( $targetName, -5 ) !== '.html' ) {
        return $contents;
    }

    switch( basename( $targetName ) ) {
        case 'input.html':
            break;
        case 'expected_output.html':
            // Don't use empty value for some tags.
            $contents = preg_replace( '/(⚡|async|amp-boilerplate)=""/', '\1', $contents );
            // DOMDocument always adds double quotes around attributes.
            $contents = preg_replace( '/(\s+[a-zA-Z-_]+)=(?!")([a-zA-Z-_.]+)/', '\1="\2"', $contents );
            break;
        default:
    }

    return $contents;
}

ensureDirExists($targetFolder);

$tmpFile = fopen($tmpFilePath, 'wb');

if (! flock($tmpFile, LOCK_EX)) {
    abort("Couldn't download the ZIP file from GitHub.");
}

fwrite($tmpFile, file_get_contents($sourceUrl));

$zip = new ZipArchive();
$res = $zip->open($tmpFilePath);

if ($res !== true) {
    abort("Couldn't open the ZIP file downloaded from GitHub.");
}

for ($index = 0; $index < $zip->numFiles; $index++) {
    $archivedPath = $zip->statIndex($index)['name'];

    if (strpos($archivedPath, $targetSubFolder) !== 0) {
        continue;
    }

    $targetName = str_replace($targetSubFolder, '', $archivedPath);

    if (empty($targetName)) {
        continue;
    }

    $targetPath = "{$targetFolder}/{$targetName}";

    if (substr($targetPath, -1) === DIRECTORY_SEPARATOR) {
        echo "Creating folder '{$targetName}' ... ";
        ensureDirExists($targetPath);
        echo "OK.\n";
        continue;
    }

    echo "Extracting '{$targetName}' ... ";
    $targetFile = $zip->getStream($archivedPath);
    if (! $targetFile) {
        abort("Couldn't extract the file '{$targetPath}' from the ZIP file downloaded from GitHub.");
    }

    $contents = '';
    while (! feof($targetFile)) {
        $contents .= fread($targetFile, 2);
    }

    $contents = filterSpecFile($targetName, $contents);

    fclose($targetFile);
    file_put_contents($targetPath, $contents);

    echo "OK.\n";
}
cleanUp();
