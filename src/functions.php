<?php

declare(strict_types=1);

// path cannot be overridden
function path(string $str = ''): string
{
    //    $root = str_replace('/public', '', getcwd());
    // return sprintf('%s/%s', str_replace('/src', '', __DIR__), ltrim($str, '/'));
    $str = str_replace(['\\', '/./', '/../'], '/', trim($str));
    $str = preg_replace('|[^a-z0-9\*\-\_\./]|i', '', $str);
    $path = sprintf('%s/%s', PHICO_PATH_ROOT, ltrim($str, '/'));

    return $path;
}
if (!function_exists('files')) {
    function files(string $filepath): \Phico\Filesystem\Files
    {
        return new \Phico\Filesystem\Files($filepath);
    }
}
if (!function_exists('folders')) {
    function folders(string $path): \Phico\Filesystem\Folders
    {
        return new \Phico\Filesystem\Folders($path);
    }
}
