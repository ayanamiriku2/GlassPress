<?php
namespace GlassPress\Core;

/**
 * File-based cache system for shared hosting.
 */
class Cache
{
    private string $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return $default;
        }

        $data = require $file;

        if (isset($data['expires']) && $data['expires'] > 0 && $data['expires'] < time()) {
            @unlink($file);
            return $default;
        }

        return $data['value'] ?? $default;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $file = $this->getFilePath($key);
        $dir = dirname($file);

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $data = [
            'expires' => $ttl > 0 ? time() + $ttl : 0,
            'value' => $value,
        ];

        $content = '<?php return ' . var_export($data, true) . ';';
        return (bool) file_put_contents($file, $content, LOCK_EX);
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    public function flush(): void
    {
        $this->deleteDirectory($this->cachePath);
        @mkdir($this->cachePath, 0755, true);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        $subDir = substr($hash, 0, 2);
        return $this->cachePath . '/' . $subDir . '/' . $hash . '.php';
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
    }
}
