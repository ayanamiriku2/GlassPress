<?php
namespace GlassPress\Core;

/**
 * PSR-4 compatible autoloader for GlassPress.
 * Works without Composer on shared hosting.
 */
class Autoloader
{
    private static array $namespaces = [
        'GlassPress\\Core\\' => '/core/',
        'GlassPress\\App\\' => '/app/',
        'GlassPress\\Admin\\' => '/app/Admin/',
        'GlassPress\\Install\\' => '/app/Install/',
    ];

    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    public static function load(string $class): void
    {
        foreach (self::$namespaces as $prefix => $baseDir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            $relativeClass = substr($class, $len);
            $file = GLASSPRESS_ROOT . $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }

    public static function addNamespace(string $prefix, string $baseDir): void
    {
        self::$namespaces[$prefix] = $baseDir;
    }
}
