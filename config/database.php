<?php
/**
 * Database Configuration and Connection for EHRMS
 * Electronic HR Management System
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ehrms');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private $connection;
    private static $instance = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Get singleton instance of Database
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            // Enable MySQLi error reporting
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            // Create connection
            $this->connection = new mysqli(
                DB_HOST,
                DB_USERNAME,
                DB_PASSWORD,
                DB_NAME
            );
            
            // Set charset
            $this->connection->set_charset(DB_CHARSET);
            
            // Set timezone
            $this->connection->query("SET time_zone = '+00:00'");
            echo "Database connected successfully!";
            
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Get database connection
     * @return mysqli
     */
    public function getConnection() {
        // Check if connection is still alive
        if (!$this->connection->ping()) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Close database connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Execute prepared statement with parameters
     * @param string $query
     * @param array $params
     * @param string $types
     * @return mysqli_result|bool
     */
    public function executeQuery($query, $params = [], $types = '') {
        try {
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }
            
            if (!empty($params)) {
                if (empty($types)) {
                    // Auto-detect parameter types
                    $types = str_repeat('s', count($params));
                }
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Query execution failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get last inserted ID
     * @return int
     */
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * Get affected rows count
     * @return int
     */
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->connection->autocommit(false);
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->connection->commit();
        $this->connection->autocommit(true);
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->connection->rollback();
        $this->connection->autocommit(true);
    }
    
    /**
     * Escape string for safe database queries
     * @param string $string
     * @return string
     */
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }
}

/**
 * Helper function to get database instance
 * @return Database
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Helper function to get database connection
 * @return mysqli
 */
function getConnection() {
    return Database::getInstance()->getConnection();
}

// Test database connection on file include (optional - remove in production)
if (php_sapi_name() !== 'cli') {
    try {
        $db = Database::getInstance();
        // Uncomment the line below to test connection
        // echo "Database connected successfully!";
    } catch (Exception $e) {
        error_log("Database initialization failed: " . $e->getMessage());
    }
}
?>