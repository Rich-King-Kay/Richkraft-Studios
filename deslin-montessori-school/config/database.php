<?php
/**
 * Database Connection Class
 */

require_once __DIR__ . '/config.php';

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        try {
            $this->connection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME,
                DB_PORT
            );

            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception('Database Connection Error: ' . $this->connection->connect_error);
            }

            // Set charset
            $this->connection->set_charset('utf8mb4');

        } catch (Exception $e) {
            // Always log the real cause so it is never silently swallowed,
            // even when the details are hidden from the end user in production.
            self::logError('Database connection', $e->getMessage());

            if (APP_ENV === 'development') {
                die('Error: ' . $e->getMessage());
            } else {
                die('A database error occurred. Please contact the administrator.');
            }
        }
    }

    /**
     * Log a database-related error to the PHP error log.
     * Centralised so failures are recorded consistently instead of
     * being discarded at the call site.
     */
    public static function logError($context, $message) {
        error_log('[DMSMS] ' . $context . ': ' . $message);
    }

    /**
     * Get singleton instance of database connection
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get MySQLi connection object
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Prepare a statement
     */
    public function prepare($query) {
        $stmt = $this->connection->prepare($query);
        if ($stmt === false) {
            self::logError('prepare', $this->connection->error . ' -- Query: ' . $query);
        }
        return $stmt;
    }

    /**
     * Execute a prepared statement
     */
    public function execute($stmt) {
        return $stmt->execute();
    }

    /**
     * Get results from a prepared statement
     */
    public function getResults($stmt) {
        $result = $stmt->get_result();
        if ($result === false) {
            self::logError('getResults', $this->connection->error);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single row from a prepared statement
     */
    public function getRow($stmt) {
        $result = $stmt->get_result();
        if ($result === false) {
            self::logError('getRow', $this->connection->error);
            return null;
        }
        return $result->fetch_assoc();
    }

    /**
     * Get number of affected rows
     */
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }

    /**
     * Get last inserted ID
     */
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }

    /**
     * Close the connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize Database');
    }
}

?>