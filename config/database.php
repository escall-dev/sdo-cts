<?php
/**
 * Database Configuration
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 * 
 * Credentials are loaded from the .env file in the project root.
 */

// Load environment variables from .env if not already loaded
if (!function_exists('loadEnvFile')) {
    function loadEnvFile() {
        $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (is_readable($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) { continue; }
                $pos = strpos($line, '=');
                if ($pos === false) { continue; }
                $key = trim(substr($line, 0, $pos));
                $val = trim(substr($line, $pos + 1));
                if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || 
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                    $val = substr($val, 1, -1);
                }
                if (!getenv($key)) {
                    putenv("$key=$val");
                    $_ENV[$key] = $val;
                }
            }
        }
    }
}
loadEnvFile();

define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}


