<?php

declare(strict_types=1);

namespace Phico\Filesystem;



class Folders
{
    public function copy(string $from, string $to, bool $overwrite = false): void
    {
        if (!is_dir($from)) {
            throw new FilesystemException("Cannot copy '$from' to '$to' as the source folder does not exist");
        }
        if (is_dir($to)) {
            if (false === $overwrite) {
                throw new FilesystemException("Cannot copy '$from' to '$to' as the destination folder already exists");
            }
            exec("rm -rf $to");
        }
        exec("cp -r $from $to");
    }
    public function create(string $folder, int $permissions = 0775): void
    {
        if (!is_dir($folder)) {
            mkdir($folder, $permissions, true);
        }
        chmod($folder, $permissions);
    }
    public function delete(string $folder, bool $force = false): void
    {
        if ($force && is_dir($folder)) {
            exec("rm -rf $folder");
        } else {
            exec("rm -r $folder");
        }
    }
    public function exists(string $folder): bool
    {
        return is_dir($folder);
    }
    public function list(string $path): array
    {
        if (!is_dir($path)) {
            throw new FilesystemException("Cannot scan folder at '$path' as the folder does not exist");
        }
        return array_filter(scandir($path, SCANDIR_SORT_ASCENDING), function ($filename) {
            return !str_starts_with($filename, '.');
        });
    }
    public function move(string $from, string $to, bool $overwrite = false): void
    {
        if (!is_dir($from)) {
            throw new FilesystemException("Cannot move '$from' to '$to' as the source folder does not exist");
        }
        if (is_dir($to)) {
            if (false === $overwrite) {
                throw new FilesystemException("Cannot move '$from' to '$to' as the destination folder already exists");
            }
            exec("rm -rf $to");
        }
        exec("mv -r $from $to");
    }
    public function rename(string $from, string $to, bool $overwrite = false): void
    {
        $this->move($from, $to, $overwrite);
    }
}

