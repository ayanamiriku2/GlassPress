<?php
namespace GlassPress\Core;

/**
 * Database abstraction using PDO.
 * Shared-hosting friendly - MySQL/MariaDB only.
 */
class Database
{
    private ?\PDO $pdo = null;
    private string $prefix;
    private int $queryCount = 0;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->prefix = $config['prefix'] ?? 'gp_';
    }

    public function connect(): \PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $host = $this->config['host'] ?? '127.0.0.1';
        $port = $this->config['port'] ?? 3306;
        $dbname = $this->config['name'] ?? '';
        $username = $this->config['username'] ?? '';
        $password = $this->config['password'] ?? '';
        $charset = $this->config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $this->pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
        ]);

        return $this->pdo;
    }

    public function getPdo(): \PDO
    {
        return $this->connect();
    }

    public function prefix(string $table = ''): string
    {
        return $this->prefix . $table;
    }

    /**
     * Execute a query with bound parameters.
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $this->queryCount++;
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all rows.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch single row.
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Fetch single value.
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        return $this->query($sql, $params)->fetchColumn($column);
    }

    /**
     * Insert a row and return the last insert ID.
     */
    public function insert(string $table, array $data): int|string
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->prefix($table),
            implode(', ', array_map(fn($c) => "`{$c}`", $columns)),
            implode(', ', $placeholders)
        );

        $this->query($sql, array_values($data));
        return $this->connect()->lastInsertId();
    }

    /**
     * Update rows. Returns affected row count.
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->prefix($table),
            $set,
            $where
        );

        $params = array_merge(array_values($data), $whereParams);
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Delete rows. Returns affected row count.
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = sprintf('DELETE FROM %s WHERE %s', $this->prefix($table), $where);
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Check if a row exists.
     */
    public function exists(string $table, string $where, array $params = []): bool
    {
        $sql = sprintf('SELECT 1 FROM %s WHERE %s LIMIT 1', $this->prefix($table), $where);
        return (bool) $this->fetchColumn($sql, $params);
    }

    /**
     * Count rows.
     */
    public function count(string $table, string $where = '1', array $params = []): int
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s', $this->prefix($table), $where);
        return (int) $this->fetchColumn($sql, $params);
    }

    /**
     * Begin a transaction.
     */
    public function beginTransaction(): bool
    {
        return $this->connect()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connect()->commit();
    }

    public function rollBack(): bool
    {
        return $this->connect()->rollBack();
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * Test connection without throwing. Returns error message or null on success.
     */
    public static function testConnection(array $config): ?string
    {
        try {
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? 3306;
            $dbname = $config['name'] ?? '';
            $username = $config['username'] ?? '';
            $password = $config['password'] ?? '';
            $charset = $config['charset'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
            new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
            ]);
            return null;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
}
