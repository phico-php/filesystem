<?php

declare(strict_types=1);

namespace Phico\Filesystem;


class Files
{
    /**
     * Append to an existing file, creating the file and folders if necessary
     * @example files()->append('storage/logs/app.log', 'Another message');
     */
    public function append(string $filepath, string $content): void
    {
        if (!file_exists($filepath)) {
            $this->create($filepath);
        }
        file_put_contents($filepath, $content, FILE_APPEND);
    }
    public function copy(string $from, string $to, bool $overwrite = false): void
    {
        if (false === $overwrite and file_exists($to)) {
            throw new FilesystemException("Cannot copy '$from' to '$to' as the destination file already exists");
        }
        if (!file_exists($from)) {
            throw new FilesystemException("Cannot copy '$from' to '$to' as the source file does not exist");
        }
        rename($from, $to);
    }
    public function create(string $filepath): void
    {
        if (!file_exists($filepath)) {
            $folder = dirname($filepath);
            if (!is_dir($folder)) {
                if (false === mkdir($folder, 0775, true)) {
                    throw new FilesystemException("Cannot create folder at $folder, check permissions?");
                }
            }
        }
        if (false === touch($filepath)) {
            throw new FilesystemException("Failed to create file at $filepath");
        }
    }
    public function delete(string $filepath): void
    {
        if (file_exists($filepath)) {
            if (false === unlink($filepath)) {
                throw new FilesystemException("Cannot delete file at $filepath, check permissions?");
            }
        }
    }
    public function exists(string $filepath): bool
    {
        return file_exists($filepath);
    }
    public function lines(string $filepath): array
    {
        if (!file_exists($filepath)) {
            throw new FilesystemException("Cannot read file at '$filepath' as the file does not exist");
        }
        $lines = file($filepath);
        if (false === $lines) {
            throw new FilesystemException("Failed to read file at '$filepath'");
        }
        return $lines;
    }
    /**
     * Returns the mime type of a file
     */
    public function mime(string $filepath): string
    {
        try {

            $fp = finfo_open(FILEINFO_MIME_TYPE);
            return finfo_file($fp, $filepath);

        } catch (\Throwable $th) {
            throw new FilesystemException("Cannot get mime info on file '$filepath'");
        } finally {
            finfo_close($fp);
        }

        finfo_close($fp);
    }
    /**
     * Move a file to a different folder, creating the destination folders if necessary
     * @example $files->move('path/to/old/file.txt', 'path/to/new');  moves path/to/old/file.txt to path/to/new/file.txt
     */
    public function move(string $from, string $to, bool $overwrite = false): void
    {
        $filename = basename($from);
        $to = dirname($to);

        if (false === $overwrite and file_exists("$to/$filename")) {
            throw new FilesystemException("Cannot move '$from' to '$to' as the a file with that name already exists in the destination folder");
        }
        if (!file_exists($from)) {
            throw new FilesystemException("Cannot move '$from' to '$to' as the source file does not exist");
        }
        if (false === rename($from, "$to/$filename")) {
            throw new FilesystemException("Failed to move file from $from to $to/$filename");
        }
    }
    public function read(string $filepath): string
    {
        if (!file_exists($filepath)) {
            throw new FilesystemException("Cannot read file at '$filepath' as the file does not exist");
        }
        $content = file_get_contents($filepath);
        if (false === $content) {
            throw new FilesystemException("Failed to read file at '$filepath'");
        }
        return $content;
    }
    public function rename(string $filepath, string $to, bool $overwrite = false): void
    {
        $to = basename($to);
        $folder = dirname($filepath);

        if (false === $overwrite and file_exists("$folder/$to")) {
            throw new FilesystemException("Cannot rename '$filepath' to '$to' as a file with that name already exists");
        }
        if (!file_exists($filepath)) {
            throw new FilesystemException("Cannot rename '$filepath' to '$to' as the file does not exist");
        }
        if (false === rename($filepath, "$folder/$to")) {
            throw new FilesystemException("Failed to rename $filepath to $folder/$to");
        }
    }
    /**
     * Write to a file, creating the file and folders if necessary, careful this will overwrite any existing content in the file
     * @example $files->write('storage/logs/app.log', 'The only message');
     */
    public function write(string $filepath, string $content): void
    {
        if (!file_exists($filepath)) {
            $this->create($filepath);
        }

        if (false === file_put_contents($filepath, $content)) {
            throw new FilesystemException("Failed to write to $filepath");
        }
    }
}

