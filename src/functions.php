<?php

declare(strict_types=1);

if (!function_exists('files')) {
    function files(): \Phico\Filesystem\Files
    {
        return new \Phico\Filesystem\Files;
    }
}
if (!function_exists('folders')) {
    function folders(): \Phico\Filesystem\Folders
    {
        return new \Phico\Filesystem\Folders;
    }
}
