<?php

declare(strict_types=1);

namespace Phico\Filesystem;



class Folders
{
    private string $folder;


    public function __construct(string $folder)
    {
        $this->folder = $folder;
    }
    public function __toString(): string
    {
        return $this->folder;
    }
    public function copy(string $to, bool $overwrite = false): Folders
    {
        if (!is_dir($this->folder)) {
            throw new FilesystemException("Cannot copy '$this->folder' to '$to' as the source folder does not exist");
        }
        if (is_dir($to)) {
            if (false === $overwrite) {
                throw new FilesystemException("Cannot copy '$this->folder' to '$to' as the destination folder already exists");
            }
            exec("rm -rf $to");
        }
        exec("cp -r $this->folder $to");

        return new Folders($to);
    }
    public function create(int $permissions = 0775): self
    {
        if (!is_dir($this->folder)) {
            mkdir($this->folder, $permissions, true);
        }
        chmod($this->folder, $permissions);

        return $this;
    }
    public function delete(bool $force = false): void
    {
        if ($force && is_dir($this->folder)) {
            exec("rm -rf $this->folder");
        } else {
            exec("rm -r $this->folder");
        }
    }
    public function exists(): bool
    {
        return is_dir($this->folder);
    }
    public function list(): array
    {
        if (!is_dir($this->folder)) {
            throw new FilesystemException("Cannot scan folder at '$this->folder' as the folder does not exist");
        }
        return array_filter(scandir($this->folder, SCANDIR_SORT_ASCENDING), function ($filename) {
            return !str_starts_with($filename, '.');
        });
    }
    public function move(string $to, bool $overwrite = false): self
    {
        if (!is_dir($this->folder)) {
            throw new FilesystemException("Cannot move '$this->folder' to '$to' as the source folder does not exist");
        }
        if (is_dir($to)) {
            if (false === $overwrite) {
                throw new FilesystemException("Cannot move '$this->folder' to '$to' as the destination folder already exists");
            }
            exec("rm -rf $to");
        }
        if (false === rename($this->folder, $to)) {
            throw new FilesystemException("Failed to move '$this->folder' to '$to'");
        }

        // update path
        $this->folder = $to;

        return $this;
    }
    /**
     * Change the owner of a folder
     */
    public function owner(string $user, string $group = null): self
    {
        if (!is_dir($this->folder)) {
            throw new FilesystemException("Cannot change owner of folder at '$this->folder' as the folder does not exist");
        }

        $original_owner = fileowner($this->folder);
        $original_group = filegroup($this->folder);

        try {

            if (!chown($this->folder, $user)) {
                throw new FilesystemException("Failed to change the owner of the folder '$this->folder' to '$user'");
            }

            if (!is_null($group) && !chgrp($this->folder, $group)) {
                throw new FilesystemException("Failed to change the group of the folder '$this->folder' to '$group'");
            }

            return $this;

        } catch (\Throwable $th) {

            // change ownership back
            chown($this->folder, $original_owner);
            chgrp($this->folder, $original_group);

            throw $th;
        }
    }
    /**
     * Change the permissions of a folder
     */
    public function permissions(int $permissions): self
    {
        if (!is_dir($this->folder)) {
            throw new FilesystemException("Cannot change permissions of the folder at '$this->folder' as the folder does not exist");
        }
        if (!chmod($this->folder, $permissions)) {
            throw new FilesystemException("Failed to change the permissions of the folder '$this->folder' to '" . decoct($permissions) . "'");
        }

        return $this;
    }
    public function rename(string $to, bool $overwrite = false): self
    {
        $this->move($to, $overwrite);
        return $this;
    }
}

