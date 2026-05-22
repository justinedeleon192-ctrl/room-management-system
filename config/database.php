<?php
/**
 * Database Connection Class for School Management System
 * Uses PDO for secure MySQL connections with XAMPP compatibility
 */

class Database {
    // Database config can be overridden via environment variables.
    // Works with XAMPP if you set them at the system/user level.
    private $host = 'localhost';
    private $db_name = 'school_system';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $pdo;

    private function applyEnvOverrides() {
        $this->host = getenv('DB_HOST') ?: $this->host;
        $this->db_name = getenv('DB_NAME') ?: $this->db_name;
        $this->username = getenv('DB_USER') ?: $this->username;
        $this->password = getenv('DB_PASS') ?: $this->password;
        $this->charset = getenv('DB_CHARSET') ?: $this->charset;
    }

    /**
     * Database constructor - initializes PDO connection
     */
    public function __construct() {
        $this->applyEnvOverrides();
        $this->connect();
    }

    /**
     * Establish database connection using PDO
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => true
            ];

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Log error and display user-friendly message
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your XAMPP MySQL server.");
        }
    }

    /**
     * Get PDO instance
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Prepare and execute SQL statement with parameters
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function prepareAndExecute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Database query failed. Please try again.");
        }
    }

    /**
     * Fetch single record
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch multiple records
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert record and return last insert ID
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function insert($sql, $params = []) {
        $this->prepareAndExecute($sql, $params);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update record and return affected rows
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function update($sql, $params = []) {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete record and return affected rows
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function delete($sql, $params = []) {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Check if record exists
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function exists($sql, $params = []) {
        $result = $this->fetch($sql, $params);
        return !empty($result);
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }

    /**
     * Close database connection
     */
    public function close() {
        $this->pdo = null;
    }

    /**
     * Get database connection status
     * @return bool
     */
    public function isConnected() {
        return $this->pdo !== null;
    }

    /**
     * Test database connection
     * @return bool
     */
    public function testConnection() {
        try {
            $stmt = $this->pdo->query("SELECT 1");
            return $stmt->fetchColumn() === 1;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
