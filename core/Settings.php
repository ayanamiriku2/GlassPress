<?php
namespace GlassPress\Core;

/**
 * Settings manager - loads from DB with file-based cache.
 */
class Settings implements \ArrayAccess
{
    private Database $db;
    private array $settings = [];
    private bool $loaded = false;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        // Try file cache first
        $cacheFile = GLASSPRESS_ROOT . '/storage/cache/settings.php';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 300) {
            $this->settings = require $cacheFile;
            $this->loaded = true;
            return;
        }

        try {
            $rows = $this->db->fetchAll(
                sprintf('SELECT setting_key, setting_value, autoload FROM %s WHERE autoload = 1', $this->db->prefix('settings'))
            );

            foreach ($rows as $row) {
                $this->settings[$row['setting_key']] = $this->maybeUnserialize($row['setting_value']);
            }

            // Write cache
            $cacheDir = dirname($cacheFile);
            if (is_dir($cacheDir) && is_writable($cacheDir)) {
                file_put_contents($cacheFile, '<?php return ' . var_export($this->settings, true) . ';', LOCK_EX);
            }
        } catch (\Exception $e) {
            // Settings table might not exist yet
        }

        $this->loaded = true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->load();

        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }

        // Try loading from DB directly (non-autoload setting)
        try {
            $value = $this->db->fetchColumn(
                sprintf('SELECT setting_value FROM %s WHERE setting_key = ?', $this->db->prefix('settings')),
                [$key]
            );

            if ($value !== false) {
                $this->settings[$key] = $this->maybeUnserialize($value);
                return $this->settings[$key];
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return $default;
    }

    public function set(string $key, mixed $value, bool $autoload = true): void
    {
        $serialized = is_string($value) ? $value : json_encode($value);

        $exists = $this->db->exists('settings', 'setting_key = ?', [$key]);

        if ($exists) {
            $this->db->update('settings', [
                'setting_value' => $serialized,
                'autoload' => $autoload ? 1 : 0,
            ], 'setting_key = ?', [$key]);
        } else {
            $this->db->insert('settings', [
                'setting_key' => $key,
                'setting_value' => $serialized,
                'autoload' => $autoload ? 1 : 0,
            ]);
        }

        $this->settings[$key] = $value;
        $this->clearCache();
    }

    public function delete(string $key): void
    {
        $this->db->delete('settings', 'setting_key = ?', [$key]);
        unset($this->settings[$key]);
        $this->clearCache();
    }

    public function getAll(): array
    {
        $this->load();
        return $this->settings;
    }

    public function clearCache(): void
    {
        $cacheFile = GLASSPRESS_ROOT . '/storage/cache/settings.php';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
        $this->loaded = false;
        $this->settings = [];
    }

    private function maybeUnserialize(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
            return $decoded;
        }

        return $value;
    }

    // ArrayAccess implementation
    public function offsetExists(mixed $offset): bool
    {
        $this->load();
        return array_key_exists($offset, $this->settings) || $this->get($offset) !== null;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->delete($offset);
    }
}
