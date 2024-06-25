<?php

use Phico\Filesystem\Files;
use Phico\Filesystem\FilesystemException;

beforeEach(function () {
    // Set up a temporary directory for testing
    $this->tmp = sys_get_temp_dir() . '/phico-php/tests/' . md5(microtime(true));
    if (!is_dir($this->tmp)) {
        mkdir($this->tmp);
    }
});
afterEach(function () {
    // Clean up the temporary directory after each test
    // array_map('unlink', glob("$this->tmp/*.*"));
    folders($this->tmp)->delete(true);
});

test('can append to a file', function () {
    $path = "$this->tmp/test.log";
    $files = new Files($path);

    $files->append('First line');
    $files->append('Second line');

    $content = file_get_contents($path);
    expect($content)->toContain('First line')
        ->toContain('Second line');
});

test('can copy a file', function () {
    $src = "$this->tmp/source.txt";
    $dst = "$this->tmp/destination.txt";
    $files = new Files($src);

    file_put_contents($src, 'Some content');
    $new = $files->copy($dst);

    expect(file_exists($dst))
        ->toBeTrue()
        ->and(file_get_contents($dst))
        ->toBe('Some content');

    expect((string) $files)->toBe($src);
    expect((string) $new)->toBe($dst);
});

test('can create a file', function () {
    $path = "$this->tmp/newfile.txt";
    $files = new Files($path);

    $files->create();

    expect(file_exists($path))->toBeTrue();
});

test('can delete a file', function () {
    $path = "$this->tmp/delete.txt";
    $files = new Files($path);

    file_put_contents($path, 'To be deleted');
    $files->delete();

    expect(file_exists($path))->toBeFalse();
});

test('can check if a file exists', function () {
    $path = "$this->tmp/exists.txt";
    $files = new Files($path);

    file_put_contents($path, 'Check existence');

    expect($files->exists($path))->toBeTrue()
        ->and(files("$this->tmp/nonexistent.txt")->exists())->toBeFalse();
});

test('can read lines from a file', function () {
    $path = "$this->tmp/lines.txt";
    $files = new Files($path);

    file_put_contents($path, "Line 1\nLine 2\nLine 3");
    $lines = $files->lines();

    expect($lines)->toBe(['Line 1', 'Line 2', 'Line 3']);
});

test('can move a file', function () {
    $src = "$this->tmp/move_source.txt";
    $dst = "$this->tmp/new-folder/move_destination.txt";
    $files = new Files($src);

    file_put_contents($src, 'Move this content');
    $files->move($dst);

    expect(file_exists($src))->toBeFalse()
        ->and(file_exists($dst))->toBeTrue()
        ->and(file_get_contents($dst))->toBe('Move this content');
});

test('can read a file', function () {
    $path = "$this->tmp/read.txt";
    $files = new Files($path);

    file_put_contents($path, 'Read this content');
    $content = $files->read();

    expect($content)->toBe('Read this content');
});

test('can rename a file', function () {
    $src = "$this->tmp/rename_source.txt";
    $dst = "$this->tmp/renamed.txt";
    $files = new Files($src);

    file_put_contents($src, 'Rename this content');
    $files->rename($dst);

    expect(file_exists($src))->toBeFalse()
        ->and(file_exists($dst))->toBeTrue()
        ->and(file_get_contents($dst))->toBe('Rename this content');
});

test('can write to a file', function () {
    $path = "$this->tmp/write.txt";
    $files = new Files($path);

    $files->write('Write this content');

    expect(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toBe('Write this content');
});
test('can write to a file then read it', function () {
    $path = "$this->tmp/write-read.txt";
    $files = new Files($path);
    $content = 'Write this content and read it back again';

    $read = $files->write($content)->read();

    expect(file_exists($path))
        ->toBeTrue()
        ->and(file_get_contents($path))
        ->toBe($content);

    expect($read)->toBe($content);
});

test('throws an exception when copying a file that already exists without overwrite', function () {
    $src = "$this->tmp/source.txt";
    $dst = "$this->tmp/destination.txt";
    $files = new Files($src);

    file_put_contents($src, 'Source content');
    file_put_contents($dst, 'Existing content');

    expect(fn() => $files->copy($dst))->toThrow(FilesystemException::class);
});

test('throws an exception when copying a non-existent file', function () {
    $src = "$this->tmp/nonexistent_source.txt";
    $dst = "$this->tmp/destination.txt";
    $files = new Files($src);

    expect(fn() => $files->copy($dst))->toThrow(FilesystemException::class);
});

test('throws an exception when moving a non-existent file', function () {
    $src = "$this->tmp/nonexistent_source.txt";
    $dst = "$this->tmp/destination.txt";
    $files = new Files($src);

    expect(fn() => $files->move($dst))->toThrow(FilesystemException::class);
});

test('throws an exception when reading a non-existent file', function () {
    $path = "$this->tmp/nonexistent.txt";
    $files = new Files($path);

    expect(fn() => $files->read())->toThrow(FilesystemException::class);
});

test('throws an exception when renaming a non-existent file', function () {
    $src = "$this->tmp/nonexistent_source.txt";
    $dst = "$this->tmp/renamed.txt";
    $files = new Files($src);

    expect(fn() => $files->rename($dst))->toThrow(FilesystemException::class);
});
