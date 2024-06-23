<?php

declare(strict_types=1);

// these functions cannot be overridden at the moment
function config(string $name, mixed $default = null): mixed
{
    static $config;
    $config = ($config) ? $config : new \Phico\Config\Config();
    return $config($name, $default);
}
function env(string $name, mixed $default = null): mixed
{
    static $env;
    $env = ($env) ? $env : new \Phico\Config\Env();
    return $env($name, $default);
}
function path(string $str = ''): string
{
    $root = str_replace('/public', '', getcwd());
    // return sprintf('%s/%s', str_replace('/src', '', __DIR__), ltrim($str, '/'));
    $str = str_replace(['\\', '/./', '/../'], '/', trim($str));
    $str = preg_replace('|[^a-z0-9\*\-\_\./]|i', '', $str);
    $path = sprintf('%s/%s', $root, ltrim($str, '/'));

    return $path;
}
function pico(): \Phico\Phico
{
    static $app;
    $app = ($app) ? $app : new \Phico\Phico();
    return $app;
}

if (!function_exists('cookie')) {
    /**
     * @param string $name
     * @param string $value
     * @param array<string,mixed> $options
     */
    function cookie(string $name, string $value, array $options = []): \Phico\Http\Cookie
    {
        return new \Phico\Http\Cookie($name, $value, $options);
    }
}
if (!function_exists('db')) {
    // db requires the name of the connection to use
    function db(string $conn = null): \Phico\Database\DB
    {
        // fetch default connection name if not provided
        $conn = (is_null($conn)) ? config("database.use") : $conn;

        // fetch connection details
        $config = (object) config("database.connections.$conn");

        // try to create PDO connection using config details
        try {
            $pdo = new PDO($config->dsn, $config->username, $config->password, $config->options = []);
            return new \Phico\Database\DB($pdo);
        } catch (PDOException $e) {
            logger()->error(sprintf('Failed to connect to the database, %s in %s line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
            throw $e;
        }
    }
}
if (!function_exists('dd')) {
    function dd()
    {
        do {
            ob_end_clean();
        } while (ob_get_level() > 0);

        echo '<pre>';
        var_dump(func_get_args());
        echo '</pre>';
        exit();
    }
}
if (!function_exists('email')) {
    function email(): \Phico\Mail\Email
    {
        return new \Phico\Mail\Email();
    }
}
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
if (!function_exists('http')) {
    function http(): \Phico\Http\Client\Http
    {
        return new \Phico\Http\Client\Http;
    }
}
if (!function_exists('logger')) {
    function logger(): \Phico\Logger\Logger
    {
        static $logger;
        $logger = ($logger) ? $logger : new \Phico\Logger\Logger();
        return $logger;
    }
}
if (!function_exists('mailer')) {
    function mailer(): \Phico\Mail\Mailer
    {
        return new \Phico\Mail\Mailer();
    }
}
if (!function_exists('request')) {
    function request(array $server, array $body = [], array $uploads = []): \Phico\Http\Request
    {
        return new \Phico\Http\Request($server, $body, $uploads);
    }
}
if (!function_exists('response')) {
    function response(int $status_code = 200): \Phico\Http\Response
    {
        return new \Phico\Http\Response($status_code);
    }
}
if (!function_exists('view')) {
    function view(): \Phico\Blayde\Blayde
    {
        $blayde = new \Phico\Blayde\Blayde();
        return $blayde
            ->paths('cache', 'storage/views')
            ->paths('views', [
                'app/views'
            ]);
    }
}
