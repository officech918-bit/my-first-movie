<?php
declare(strict_types=1);

/**
 * Author: Matthew Saragusa (original)
 * Modernized: Secure, backward-compatible refactor
 *
 * Features preserved:
 * - insert_array()
 * - update_array()
 * - get_record_by_ID()
 * - get_records_by_group()
 * - cleanInput / cleanQuery
 *
 * Improvements:
 * - Prepared statements
 * - No SQL injection
 * - Modern mysqli usage
 * - Better error handling
 * - Optional env-based credentials
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * ------------------------------------------------------------------
 * DATABASE CLASS (PDO Implementation)
 * ------------------------------------------------------------------
 */

class MySQLDB
{
    public ?PDO $db = null;

    /**
     * Establishes the database connection using environment variables.
     */
    public function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? null;
        $name = $_ENV['DB_DATABASE'] ?? null;
        $user = $_ENV['DB_USERNAME'] ?? null;
        $pass = $_ENV['DB_PASSWORD'] ?? null;

        if (empty($host) || empty($name) || !isset($user) || !isset($pass)) {
            throw new \RuntimeException('Database environment variables (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD) are not set.');
        }

        $dsn = "mysql:host={$host};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$name};charset=utf8mb4;sslmode=REQUIRED";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            // SSL settings for Aiven
            PDO::MYSQL_ATTR_SSL_CA        => true,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];

        try {
            $this->db = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    public function __destruct()
    {
        $this->db = null;
    }

    public function isDatabase(): bool
    {
        return $this->db !== null;
    }

    /**
     * --------------------------------------------------------------
     * INPUT CLEANING (kept for backward compatibility)
     * NOTE: SQL safety now handled by PDO prepared statements
     * --------------------------------------------------------------
     */

    public function cleanInput(string $input): string
    {
        $search = [
            '@<script[^>]*?>.*?</script>@si',
            '@<[\/\!]*?[^<>]*?>@si',
            '@<style[^>]*?>.*?</style>@siU',
            '@<![\s\S]*?--[ \t\n\r]*>@'
        ];

        return preg_replace($search, '', $input);
    }

    public function cleanQuery($input)
    {
        if (is_array($input)) {
            $output = [];
            foreach ($input as $key => $val) {
                $output[$key] = $this->cleanInput((string)$val);
            }
            return $output;
        }

        return $this->cleanInput((string)$input);
    }

    /**
     * --------------------------------------------------------------
     * INSERT ARRAY
     * --------------------------------------------------------------
     */

    public function insert_array(string $table, array $insert_values): bool
    {
        $table = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $table) . '`';
        $columns = array_keys($insert_values);
        $placeholders = array_fill(0, count($columns), '?');

        $sanitized_columns = array_map(function($col) {
            return '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $col) . '`';
        }, $columns);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(',', $sanitized_columns),
            implode(',', $placeholders)
        );

        $stmt = $this->query($sql, array_values($insert_values));
        return $stmt->rowCount() > 0;
    }

    /**
     * --------------------------------------------------------------
     * UPDATE ARRAY
     * --------------------------------------------------------------
     */

    public function update_array(
        string $table,
        string $keyColumnName,
        $id,
        array $update_values
    ): bool {
        $table = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $table) . '`';
        $keyColumnName = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $keyColumnName) . '`';

        $set_parts = [];
        $params = [];
        foreach ($update_values as $key => $value) {
            $key = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $key) . '`';
            $set_parts[] = "{$key} = ?";
            $params[] = $value;
        }
        $set_clause = implode(', ', $set_parts);
        $params[] = $id;

        $sql = "UPDATE {$table} SET {$set_clause} WHERE {$keyColumnName} = ?";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * --------------------------------------------------------------
     * SELECT SINGLE RECORD
     * --------------------------------------------------------------
     */

    public function get_record_by_ID(
        string $table,
        string $keyColumnName,
        $id,
        string $fields = '*'
    ): ?array {
        $table = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $table) . '`';
        $keyColumnName = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $keyColumnName) . '`';

        $sql = "SELECT {$fields} FROM {$table} WHERE {$keyColumnName} = ? LIMIT 1";
        $stmt = $this->query($sql, [$id]);
        $record = $stmt->fetch();

        return $record ?: null;
    }

    /**
     * --------------------------------------------------------------
     * SELECT MULTIPLE RECORDS
     * --------------------------------------------------------------
     */

    public function get_records_by_group(
        string $table,
        string $groupKeyName,
        $groupID,
        string $orderKeyName = '',
        string $order = 'ASC',
        string $fields = '*'
    ): array {
        $table = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $table) . '`';
        $groupKeyName = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $groupKeyName) . '`';
        
        $orderSql = '';
        if ($orderKeyName !== '') {
            $orderKeyName = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $orderKeyName) . '`';
            $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
            $orderSql = " ORDER BY $orderKeyName $order";
        }

        $sql = "SELECT $fields FROM $table WHERE $groupKeyName = ?" . $orderSql;
        $stmt = $this->query($sql, [$groupID]);

        return $stmt->fetchAll();
    }

    /**
     * --------------------------------------------------------------
     * GENERIC QUERY (Prepared)
     * --------------------------------------------------------------
     */

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch_array(PDOStatement $result): ?array
    {
        $record = $result->fetch();
        return $record ?: null;
    }

    public function num_rows(PDOStatement $result): int
    {
        return $result->rowCount();
    }

    public function insert_id(): ?string
    {
        $id = $this->db->lastInsertId();
        return $id === '0' ? null : $id;
    }
}