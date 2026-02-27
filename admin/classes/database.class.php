<?php

/**
 * A Modern, Secure Database Class using PDO.
 *
 * This class provides a secure and easy-to-use interface for database operations
 * using PHP Data Objects (PDO). It enforces the use of prepared statements
 * to prevent SQL injection attacks.
 */
class MySQLDB
{
    /**
     * @var PDO|null The singleton PDO connection instance.
     */
    private static ?PDO $instance = null;

    /**
     * The constructor is private to prevent direct creation of the object.
     * It establishes the database connection.
     *
     * @param string $host
     * @param string $dbname
     * @param string $username
     * @param string $password
     */
    private function __construct(
        private string $host = 'localhost',
        private string $dbname = 'myfirstm_live',
        private string $username = 'root',
        private string $password = ''
    ) {
        // DSN (Data Source Name)
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
        ];

        try {
            self::$instance = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // In a real application, you would log this error, not display it.
            // For development, this is fine.
            throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Gets the singleton PDO instance.
     *
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // In a real app, you'd pass credentials from a config file
            new self();
        }
        return self::$instance;
    }

    /**
     * Executes a prepared statement and returns the statement object.
     * This is the primary method for all database interactions.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params The parameters to bind to the query.
     * @return PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // In production, log the error. Avoid echoing details to the user.
            // For development, re-throwing the exception provides a clear stack trace.
            throw new PDOException("Query failed: " . $e->getMessage() . " (SQL: $sql)", (int)$e->getCode());
        }
    }

    /**
     * Inserts an array of data into a table.
     *
     * @param string $table The name of the table.
     * @param array $data An associative array of data (column => value).
     * @return string|false The ID of the last inserted row or false on failure.
     */
    public static function insert(string $table, array $data): string|false
    {
        if (empty($data)) {
            return false;
        }

        // Use backticks to escape table and column names
        $table = "`" . str_replace("`", "``", $table) . "`";
        $columns = implode(', ', array_map(fn($col) => "`" . str_replace("`", "``", $col) . "`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        self::query($sql, array_values($data));

        return self::getInstance()->lastInsertId();
    }

    /**
     * Updates a record in the table based on a key column.
     *
     * @param string $table The name of the table.
     * @param int|string $id The ID of the record to update.
     * @param array $data An associative array of data to update (column => value).
     * @param string $keyColumn The name of the primary key column.
     * @return int The number of affected rows.
     */
    public static function update(string $table, int|string $id, array $data, string $keyColumn = 'id'): int
    {
        if (empty($data)) {
            return 0;
        }

        // Use backticks to escape table and column names
        $table = "`" . str_replace("`", "``", $table) . "`";
        $keyColumn = "`" . str_replace("`", "``", $keyColumn) . "`";
        $setClauses = implode(', ', array_map(fn($col) => "`" . str_replace("`", "``", $col) . "` = ?", array_keys($data)));

        $sql = "UPDATE {$table} SET {$setClauses} WHERE {$keyColumn} = ?";

        $params = array_values($data);
        $params[] = $id;

        $stmt = self::query($sql, $params);

        return $stmt->rowCount();
    }

    /**
     * A convenience method to fetch a single record.
     *
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public static function fetchOne(string $sql, array $params = []): array|false
    {
        return self::query($sql, $params)->fetch();
    }

    /**
     * A convenience method to fetch all records.
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Prevents cloning of the instance.
     */
    private function __clone()
    {
    }

    /**
     * Prevents unserialization of the instance.
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}