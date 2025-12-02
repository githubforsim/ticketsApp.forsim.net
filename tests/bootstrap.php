<?php
/**
 * Bootstrap file for PHPUnit tests
 * Initializes the testing environment
 */

// Load Composer autoloader if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Define constants for testing
define('TESTS_PATH', __DIR__);
define('APP_PATH', dirname(__DIR__) . '/ticketsApp');

// Load application constants
require_once APP_PATH . '/config/constants.php';

// Override database name for testing
if (!defined('DB_NAME_TEST')) {
    define('DB_NAME_TEST', getenv('DB_NAME') ?: 'ticketsApp_test');
}

// Load database functions
require_once APP_PATH . '/config/database.php';

// Helper function to get test database connection
function getTestDbConnection(): PDO
{
    try {
        $db = new PDO(
            'mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME_TEST . ';charset=utf8mb4',
            DB_USER,
            DB_PASSWORD
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        echo "Test DB Connection failed: " . $e->getMessage() . "\n";
        // Fallback to regular database if test DB doesn't exist
        return dbConnect();
    }
}

// Helper to clean up test data
function cleanupTestData(PDO $db, string $table, string $condition = ''): void
{
    try {
        $sql = "DELETE FROM {$table}";
        if ($condition) {
            $sql .= " WHERE {$condition}";
        }
        $db->exec($sql);
    } catch (PDOException $e) {
        // Silently ignore cleanup errors
    }
}
