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
            if (APP_ENV === 'development') {
                die('Error: ' . $e->getMessage());
            } else {
                die('A database error occurred. Please contact the administrator.');
            }
        }
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
        return $this->connection->prepare($query);
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
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get a single row from a prepared statement
     */
    public function getRow($stmt) {
        $result = $stmt->get_result();
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