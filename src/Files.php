<?php

declare(strict_types=1);

namespace Phico\Filesystem;


class Files
{
    private string $filepath;
    private string $folder;
    private string $fullname;
    private string $filename;
    private string $extension;


    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
        $this->setMeta();
    }
    public function __toString(): string
    {
        return $this->filepath;
    }
    /**
     * Append to an existing file, creating the file and folders if necessary
     * @example files('storage/logs/app.log')->append('Another message');
     */
    public function append(string $content): self
    {
        if (!file_exists($this->filepath)) {
            $this->create();
        }
        if (false === file_put_contents($this->filepath, $content, FILE_APPEND | LOCK_EX)) {
            throw new FilesystemException("Failed to append to file at $this->filepath");
        }

        return $this;
    }
    public function copy(string $to, bool $overwrite = false): Files
    {
        if (false === $overwrite and file_exists($to)) {
            throw new FilesystemException("Cannot copy '$this->filepath' to '$to' as the destination file already exists");
        }
        if (!file_exists($this->filepath)) {
            throw new FilesystemException("Cannot copy '$this->filepath' to '$to' as the source file does not exist");
        }
        if (!copy($this->filepath, $to)) {
            throw new FilesystemException("Failed to copy file from $this->filepath to $to");
        }

        return new Files($to);
    }
    public function create(): self
    {
        if (!file_exists($this->filepath)) {
            $folder = dirname($this->filepath);
            if (!is_dir($folder)) {
                if (false === mkdir($folder, 0775, true)) {
                    throw new FilesystemException("Cannot create folder at $folder, check permissions?");
                }
            }
        }
        if (false === touch($this->filepath)) {
            throw new FilesystemException("Failed to create file at $this->filepath");
        }

        return $this;
    }
    public function delete(): void
    {
        if (file_exists($this->filepath)) {
            if (false === unlink($this->filepath)) {
                throw new FilesystemException("Cannot delete file at $this->filepath, check permissions?");
            }
        }
    }
    public function exists(): bool
    {
        return file_exists($this->filepath);
    }
    public function mime(): Mime
    {
        return new Mime($this->filepath);
    }
    public function mtime(): int
    {
        if (!file_exists($this->filepath)) {
            throw new FilesystemException("Cannot get modification time of file '$this->filepath' as the file does not exist");
        }

        return filemtime($this->filepath);
    }
    public function lines(): array
    {
        if (!file_exists($this->filepath)) {
            throw new FilesystemException("Cannot read file at '$this->filepath' as the file does not exist");
        }
        $lines = file($this->filepath, FILE_IGNORE_NEW_LINES);
        if (false === $lines) {
            throw new FilesystemException("Failed to read file at '$this->filepath'");
        }
        return $lines;
    }
    /**
     * Move a file to a different folder, creating the destination folders if necessary
     * @example $files('path/to/old/file.txt')->move('path/to/new');  moves path/to/old/file.txt to path/to/new/file.txt
     */
    public function move(string $dst, bool $overwrite = false): void
    {
        if (false === $overwrite and file_exists($dst)) {
            throw new FilesystemException("Cannot move '$this->filepath' to '$dst' as a file with that name already exists in the destination folder");
        }
        if (!file_exists($this->filepath)) {
            throw new FilesystemException("Cannot move '$this->filepath' to '$dst' as the source file does not exist");
        }

        // create file at dst (recusively creating folders)
        files($dst)->create();

        // move this file to dst
        if (false === rename($this->filepath, "$dst")) {
            throw new FilesystemException("Failed to move file from $this->filepath to $dst");
        }

        // update filepath to new location
        $this->filepath = $dst;
        $this->setMeta();
    }
    /**
     * Change the owner of a file
     */
    public function owner(string $user, string $group = null): self
    {
        if (!file_exists($this->filepath)) {
            throw new FilesystemException("Cannot change owner of file at '$this->filepath' as the file does not exist");
        }

        $original_owner = fileowner($this->filepath);
        $original_group = filegroup($this->filepath);

        try {

            if (!chown($this->filepath, $user)) {
                throw new FilesystemException("Failed to change the owner of the file '$this->filepath' to '$user'");
            }

            if (!is_null($group) && !chgrp($this->filepath, $group)) {
                throw new FilesystemException("Failed to change the group of the file '$this->filepath' to '$group'");
            }

            return $this;

        } catch (\Throwable $th) {

            // change ownership back
            chown($this->filepath, $original_owner);
            chgrp($this->filepath, $original_group);

            throw $th;
        }
    }
    /**
     * Change the permissions of a file
     */
    public function permissions(int $permissions): self
    {
        if (!file_exists($this->filepath)) {
            throw new FilesystemException("Cannot change permissions of the file at '$this->filepath' as the file does not exist");
        }

        $original_permissions = fileperms($this->filepath);

        if (!chmod($this->filepath, $permissions)) {
            throw new FilesystemException("Failed to change the permissions of the file '$this->filepath' to '" . decoct($permissions) . "'");
        }

        return $this;
    }
    public function read(): string
    {
        if (!file_exists($this->filepath)) {
            throw new FilesystemException("Cannot read file at '$this->filepath' as the file does not exist");
        }
        $content = file_get_contents($this->filepath);
        if (false === $content) {
            throw new FilesystemException("Failed to read file at '$this->filepath'");
        }
        return $content;
    }
    public function rename(string $to, bool $overwrite = false): void
    {
        $to = basename($to);
        $folder = dirname($this->filepath);

        if (false === $overwrite and file_exists("$folder/$to")) {
            throw new FilesystemException("Cannot rename '$this->filepath' to '$to' as a file with that name already exists");
        }
        if (!file_exists($this->filepath)) {
            throw new FilesystemException("Cannot rename '$this->filepath' to '$to' as the file does not exist");
        }
        if (false === rename($this->filepath, "$folder/$to")) {
            throw new FilesystemException("Failed to rename $this->filepath to $folder/$to");
        }

        // update filepath to new location
        $this->filepath = "$folder/$to";
        $this->setMeta();
    }
    /**
     * Write to a file, creating the file and folders if necessary, careful this will overwrite any existing content in the file
     * @example $files->write('storage/logs/app.log', 'The only message');
     */
    public function write(string $content): self
    {
        if (!file_exists($this->filepath)) {
            $this->create();
        }

        if (false === file_put_contents($this->filepath, $content, LOCK_EX)) {
            throw new FilesystemException("Failed to write to $this->filepath");
        }

        return $this;
    }

    private function setMeta(): void
    {
        $info = pathinfo($this->filepath);
        $this->folder = $info['dirname'] ?? '';
        $this->fullname = $info['basename'] ?? '';
        $this->filename = $info['filename'] ?? '';
        $this->extension = $info['extension'] ?? '';
    }
}

