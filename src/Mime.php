<?php

declare(strict_types=1);

namespace Phico\Filesystem;

use finfo;

class Mime
{
    protected finfo $finfo;
    protected string $filepath;
    // map of mime types to extensions
    protected array $mime_to_ext = [
        // Images
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/webp' => 'webp',
        'image/tiff' => 'tiff',
        'image/x-icon' => 'ico',

        // Audio
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'audio/ogg' => 'ogg',
        'audio/aac' => 'aac',
        'audio/flac' => 'flac',

        // Video
        'video/mp4' => 'mp4',
        'video/x-msvideo' => 'avi',
        'video/x-ms-wmv' => 'wmv',
        'video/mpeg' => 'mpeg',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',

        // Text
        'text/plain' => 'txt',
        'text/html' => 'html',
        'text/css' => 'css',
        'text/csv' => 'csv',
        'text/markdown' => 'md',
        'application/rtf' => 'rtf',

        // Documents
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/vnd.ms-access' => 'mdb',
        'application/vnd.ms-outlook' => 'msg',

        // Apple iWork
        'application/vnd.apple.pages' => 'pages',
        'application/vnd.apple.numbers' => 'numbers',
        'application/vnd.apple.keynote' => 'key',

        // OpenOffice / LibreOffice
        'application/vnd.oasis.opendocument.text' => 'odt',
        'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
        'application/vnd.oasis.opendocument.presentation' => 'odp',
        'application/vnd.oasis.opendocument.graphics' => 'odg',
        'application/vnd.oasis.opendocument.chart' => 'odc',
        'application/vnd.oasis.opendocument.formula' => 'odf',

        // Archives
        'application/zip' => 'zip',
        'application/x-tar' => 'tar',
        'application/x-7z-compressed' => '7z',
        'application/x-rar-compressed' => 'rar',
        'application/gzip' => 'gz',

        // Web formats
        'application/javascript' => 'js',
        'application/json' => 'json',
        'application/xml' => 'xml',
        'application/xhtml+xml' => 'xhtml',
        'application/x-www-form-urlencoded' => 'urlencoded',

        // Fonts
        'font/otf' => 'otf',
        'font/ttf' => 'ttf',
        'font/woff' => 'woff',
        'font/woff2' => 'woff2',

        // Others
        'application/x-sh' => 'sh',
        'application/x-bittorrent' => 'torrent',
        'application/x-msdownload' => 'exe',
        'application/x-dosexec' => 'exe',
        'application/vnd.visio' => 'vsd',
        'application/x-iso9660-image' => 'iso',
        'application/vnd.amazon.ebook' => 'azw',
        'application/epub+zip' => 'epub',
        'application/vnd.android.package-archive' => 'apk',
    ];

    public function __construct(string $filepath)
    {
        if (!file_exists($filepath)) {
            throw new FilesystemException("Cannot get mime type of file '$filepath' as the file does not exist");
        }
        $this->filepath = $filepath;

        $this->finfo = new finfo(); // automatically destructed?
        if (!$this->finfo) {
            throw new FilesystemException("Failed to open fileinfo resource");
        }
    }
    // returns all the mime encoding for the file
    public function encoding(array $map = []): ?string
    {
        $result = $this->finfo->file($this->filepath, FILEINFO_MIME_ENCODING);
        return (false === $result) ? null : $result;
    }
    // returns the mime extension for the file or null if it cannot be determined (not the filename extension)
    public function extension(array $map = []): ?string
    {
        $result = $this->finfo->file($this->filepath, FILEINFO_EXTENSION);
        if (false === $result) {
            return $this->mapTypeToExtension($map);
        }

        return $result;
    }
    // returns the mime type of the file
    public function type(): ?string
    {
        $result = $this->finfo->file($this->filepath, FILEINFO_MIME_TYPE);
        return (false === $result) ? null : $result;
    }
    // maps the mime type using a customisable map
    private function mapTypeToExtension(array $map = []): ?string
    {
        $type = $this->type();
        $map = array_merge($this->mime_to_ext, $map);

        return $map[$type] ?? null;
    }
}
