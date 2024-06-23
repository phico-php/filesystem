<?php

use Phico\Filesystem\Files;
use Phico\Filesystem\FilesystemException;

beforeEach(function () {
    // Set up a temporary directory for testing
    $this->tempDir = sys_get_temp_dir() . '/phico_test';
    if (!is_dir($this->tempDir)) {
        mkdir($this->tempDir);
    }
});

afterEach(function () {
    // Clean up the temporary directory after each test
    array_map('unlink', glob("$this->tempDir/*.*"));
    rmdir($this->tempDir);
});

test('it appends to a file', function () {
    $files = new Files();
    $filePath = "$this->tempDir/test.log";

    $files->append($filePath, 'First line');
    $files->append($filePath, 'Second line');

    $content = file_get_contents($filePath);
    expect($content)->toContain('First line')
        ->toContain('Second line');
});

test('it copies a file', function () {
    $files = new Files();
    $sourcePath = "$this->tempDir/source.txt";
    $destinationPath = "$this->tempDir/destination.txt";

    file_put_contents($sourcePath, 'Some content');
    $files->copy($sourcePath, $destinationPath);

    expect(file_exists($destinationPath))->toBeTrue()
        ->and(file_get_contents($destinationPath))->toBe('Some content');
});

test('it creates a file', function () {
    $files = new Files();
    $filePath = "$this->tempDir/newfile.txt";

    $files->create($filePath);

    expect(file_exists($filePath))->toBeTrue();
});

test('it deletes a file', function () {
    $files = new Files();
    $filePath = "$this->tempDir/delete.txt";

    file_put_contents($filePath, 'To be deleted');
    $files->delete($filePath);

    expect(file_exists($filePath))->toBeFalse();
});

test('it checks if a file exists', function () {
    $files = new Files();
    $filePath = "$this->tempDir/exists.txt";

    file_put_contents($filePath, 'Check existence');

    expect($files->exists($filePath))->toBeTrue()
        ->and($files->exists("$this->tempDir/nonexistent.txt"))->toBeFalse();
});

test('it reads lines from a file', function () {
    $files = new Files();
    $filePath = "$this->tempDir/lines.txt";

    file_put_contents($filePath, "Line 1\nLine 2\nLine 3");
    $lines = $files->lines($filePath);

    expect($lines)->toBe(['Line 1', 'Line 2', 'Line 3']);
});

test('it moves a file', function () {
    $files = new Files();
    $sourcePath = "$this->tempDir/move_source.txt";
    $destinationPath = "$this->tempDir/move_destination.txt";

    file_put_contents($sourcePath, 'Move this content');
    $files->move($sourcePath, $destinationPath);

    expect(file_exists($sourcePath))->toBeFalse()
        ->and(file_exists($destinationPath))->toBeTrue()
        ->and(file_get_contents($destinationPath))->toBe('Move this content');
});

test('it reads a file', function () {
    $files = new Files();
    $filePath = "$this->tempDir/read.txt";

    file_put_contents($filePath, 'Read this content');
    $content = $files->read($filePath);

    expect($content)->toBe('Read this content');
});

test('it renames a file', function () {
    $files = new Files();
    $sourcePath = "$this->tempDir/rename_source.txt";
    $destinationPath = "$this->tempDir/renamed.txt";

    file_put_contents($sourcePath, 'Rename this content');
    $files->rename($sourcePath, $destinationPath);

    expect(file_exists($sourcePath))->toBeFalse()
        ->and(file_exists($destinationPath))->toBeTrue()
        ->and(file_get_contents($destinationPath))->toBe('Rename this content');
});

test('it writes to a file', function () {
    $files = new Files();
    $filePath = "$this->tempDir/write.txt";

    $files->write($filePath, 'Write this content');

    expect(file_exists($filePath))->toBeTrue()
        ->and(file_get_contents($filePath))->toBe('Write this content');
});

test('it throws an exception when copying a file that already exists without overwrite', function () {
    $files = new Files();
    $sourcePath = "$this->tempDir/source.txt";
    $destinationPath = "$this->tempDir/destination.txt";

    file_put_contents($sourcePath, 'Source content');
    file_put_contents($destinationPath, 'Existing content');

    expect(fn() => $files->copy($sourcePath, $destinationPath))->toThrow(FilesystemException::class);
});

test('it throws an exception when copying a non-existent file', function () {
    $files = new Files();
    $sourcePath = "$this->tempDir/nonexistent_source.txt";
    $destinationPath = "$this->tempDir/destination.txt";

    expect(fn() => $files->copy($sourcePath, $destinationPath))->toThrow(FilesystemException::class);
});

test('it throws an exception when moving a non-existent file', function () {
    $files = new Files();
    $sourcePath = "$this->tempDir/nonexistent_source.txt";
    $destinationPath = "$this->tempDir/destination.txt";

    expect(fn() => $files->move($sourcePath, $destinationPath))->toThrow(FilesystemException::class);
});

test('it throws an exception when reading a non-existent file', function () {
    $files = new Files();
    $filePath = "$this->tempDir/nonexistent.txt";

    expect(fn() => $files->read($filePath))->toThrow(FilesystemException::class);
});

test('it throws an exception when renaming a non-existent file', function () {
    $files = new Files();
    $sourcePath = "$this->tempDir/nonexistent_source.txt";
    $destinationPath = "$this->tempDir/renamed.txt";

    expect(fn() => $files->rename($sourcePath, $destinationPath))->toThrow(FilesystemException::class);
});
