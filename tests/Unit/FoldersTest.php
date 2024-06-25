<?php

use Phico\Filesystem\Folders;
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

test('can copy a folder', function () {
    $src = $this->tmp . '/source';
    $dst = $this->tmp . '/destination';

    mkdir($src);
    $folders = new Folders($src);

    $new = $folders->copy($dst);

    expect(is_dir($dst))->toBeTrue();
    expect($new)->toBeInstanceOf(Folders::class);
    expect($new->exists())->toBeTrue();
    expect((string) $folders)->toBe($src);
    expect((string) $new)->toBe($dst);
});

test('can create a folder with specified permissions', function () {
    $path = $this->tmp . '/new_dir';
    $folders = new Folders($path);

    $folders->create(0755);

    expect(is_dir($path))->toBeTrue();
    expect(decoct(fileperms($path) & 0777))->toBe('755');
});

test('can delete a folder', function () {
    $path = $this->tmp . '/dir_to_delete';
    mkdir($path);
    $folders = new Folders($path);

    $folders->delete();

    expect(is_dir($path))->toBeFalse();
});

test('can check if a folder exists', function () {
    $path = $this->tmp . '/existing_dir';
    mkdir($path);
    $folders = new Folders($path);

    expect($folders->exists())->toBeTrue();
});

test('can list contents of a folder', function () {
    $path = $this->tmp . '/dir_with_contents';
    mkdir($path);
    file_put_contents($path . '/file1.txt', 'Content');
    file_put_contents($path . '/file2.txt', 'Content');
    $folders = new Folders($path);

    $contents = $folders->list();

    expect($contents)->toContain('file1.txt', 'file2.txt');
});

test('can move a folder', function () {
    $src = $this->tmp . '/source_dir';
    $dst = $this->tmp . '/moved_dir';

    mkdir($src);
    $folders = new Folders($src);

    $folders->move($dst);

    expect(is_dir($src))->toBeFalse();
    expect(is_dir($dst))->toBeTrue();
    expect((string) $folders)->toBe($dst);
});

test('can change owner of a folder', function () {
    $path = $this->tmp . '/dir_to_chown';
    mkdir($path);
    $folders = new Folders($path);

    // Assuming the current user can change ownership to itself
    $owner = posix_getpwuid(posix_geteuid())['name'];

    $folders->owner($owner);

    expect(fileowner($path))->toBe(posix_geteuid());
});

test('can change permissions of a folder', function () {
    $path = $this->tmp . '/dir_to_chmod';
    mkdir($path);
    $folders = new Folders($path);

    $folders->permissions(0755);

    expect(decoct(fileperms($path) & 0777))->toBe('755');
});

test('can rename a folder', function () {
    $src = $this->tmp . '/source_dir';
    $dst = $this->tmp . '/renamed_dir';

    mkdir($src);
    $folders = new Folders($src);

    $folders->rename($dst);

    expect(is_dir($src))->toBeFalse();
    expect(is_dir($dst))->toBeTrue();
    expect((string) $folders)->toBe($dst);
});
