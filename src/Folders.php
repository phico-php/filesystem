<?php

declare(strict_types=1);

namespace Phico\Filesystem;



class Folders
{
    private string $path;


    public function __construct(string $path)
    {
        $this->path = $path;
    }
    public function copy(string $to, bool $overwrite = false): Folders
    {
        if (!is_dir($this->path)) {
            throw new FilesystemException("Cannot copy '$this->path' to '$to' as the source folder does not exist");
        }
        if (is_dir($to)) {
            if (false === $overwrite) {
                throw new FilesystemException("Cannot copy '$this->path' to '$to' as the destination folder already exists");
            }
            exec("rm -rf $to");
        }
        exec("cp -r $this->path $to");

        return new Folders($to);
    }
    public function create(int $permissions = 0775): self
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, $permissions, true);
        }
        chmod($this->path, $permissions);

        return $this;
    }
    public function delete(bool $force = false): void
    {
        if ($force && is_dir($this->path)) {
            exec("rm -rf $this->path");
        } else {
            exec("rm -r $this->path");
        }
    }
    public function exists(): bool
    {
        return is_dir($this->path);
    }
    public function list(): array
    {
        if (!is_dir($this->path)) {
            throw new FilesystemException("Cannot scan folder at '$this->path' as the folder does not exist");
        }
        return array_filter(scandir($this->path, SCANDIR_SORT_ASCENDING), function ($filename) {
            return !str_starts_with($filename, '.');
        });
    }
    public function move(string $to, bool $overwrite = false): self
    {
        if (!is_dir($this->path)) {
            throw new FilesystemException("Cannot move '$this->path' to '$to' as the source folder does not exist");
        }
        if (is_dir($to)) {
            if (false === $overwrite) {
                throw new FilesystemException("Cannot move '$this->path' to '$to' as the destination folder already exists");
            }
            exec("rm -rf $to");
        }
        exec("mv -r $this->path $to");

        // update path
        $this->path = $to;

        return $this;
    }
    /**
     * Change the owner of a folder
     */
    public function owner(string $user, string $group = null): self
    {
        if (!is_dir($this->path)) {
            throw new FilesystemException("Cannot change owner of folder at '$this->path' as the folder does not exist");
        }

        $original_owner = fileowner($this->path);
        $original_group = filegroup($this->path);

        try {

            if (!chown($this->path, $user)) {
                throw new FilesystemException("Failed to change the owner of the folder '$this->path' to '$user'");
            }

            if (!is_null($group) && !chgrp($this->path, $group)) {
                throw new FilesystemException("Failed to change the group of the folder '$this->path' to '$group'");
            }

            return $this;

        } catch (\Throwable $th) {

            // change ownership back
            chown($this->path, $original_owner);
            chgrp($this->path, $original_group);

            throw $th;
        }
    }
    /**
     * Change the permissions of a folder
     */
    public function permissions(int $permissions): self
    {
        if (!is_dir($this->path)) {
            throw new FilesystemException("Cannot change permissions of the folder at '$this->path' as the folder does not exist");
        }
        if (!chmod($this->path, $permissions)) {
            throw new FilesystemException("Failed to change the permissions of the folder '$this->path' to '" . decoct($permissions) . "'");
        }

        return $this;
    }
    public function rename(string $to, bool $overwrite = false): self
    {
        $this->move($to, $overwrite);
        return $this;
    }
}

