<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../ticketsApp/config/database.php';

/**
 * Tests for database.php functions
 */
class DatabaseTest extends TestCase
{
    public function testDbConnect(): void
    {
        $db = dbConnect();

        $this->assertInstanceOf(PDO::class, $db, "dbConnect should return a PDO instance");
        
        // Test connection is working
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals(1, $result['test'], "Database query should work");
    }

    public function testDbConnectErrorMode(): void
    {
        $db = dbConnect();
        
        $errorMode = $db->getAttribute(PDO::ATTR_ERRMODE);
        
        $this->assertEquals(
            PDO::ERRMODE_EXCEPTION,
            $errorMode,
            "PDO should be configured to throw exceptions"
        );
    }

    public function testDbRequest(): void
    {
        $db = dbConnect();
        
        // Test simple query
        $result = dbRequest($db, "SELECT 'test' as value");
        
        $this->assertIsArray($result, "dbRequest should return an array");
        $this->assertNotEmpty($result, "Result should not be empty");
        $this->assertEquals('test', $result[0]['value']);
    }

    public function testDbRequestWithRealTable(): void
    {
        $db = dbConnect();
        
        // Test query on real table
        $result = dbRequest($db, "SELECT * FROM statut LIMIT 1");
        
        $this->assertIsArray($result, "dbRequest should return an array");
        
        if (!empty($result)) {
            $this->assertArrayHasKey('statut_id', $result[0]);
            $this->assertArrayHasKey('valeur', $result[0]);
        }
    }

    public function testDbRequestInvalidQuery(): void
    {
        $db = dbConnect();
        
        // Test with invalid SQL
        $result = dbRequest($db, "SELECT * FROM nonexistent_table_xyz");
        
        $this->assertFalse($result, "Invalid query should return false");
    }

    public function testDbRequestReturnsAssociativeArray(): void
    {
        $db = dbConnect();
        
        $result = dbRequest($db, "SELECT 'key1' as col1, 'key2' as col2");
        
        $this->assertArrayHasKey('col1', $result[0]);
        $this->assertArrayHasKey('col2', $result[0]);
        $this->assertEquals('key1', $result[0]['col1']);
        $this->assertEquals('key2', $result[0]['col2']);
    }

    public function testGetTestDbConnection(): void
    {
        $db = getTestDbConnection();

        $this->assertInstanceOf(PDO::class, $db, "getTestDbConnection should return a PDO instance");
        
        // Verify it's a working connection
        $stmt = $db->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotEmpty($result['db_name'], "Should be connected to a database");
    }

    public function testCleanupTestData(): void
    {
        $db = getTestDbConnection();
        
        // Create a test user
        $testUsername = 'cleanup_test_user_' . time();
        $hashedPassword = password_hash('test123', PASSWORD_BCRYPT);
        
        $stmt = $db->prepare(
            "INSERT INTO user (username, mail, entreprise, pwd, role) 
             VALUES (:username, :mail, :entreprise, :pwd, :role)"
        );
        $stmt->execute([
            'username' => $testUsername,
            'mail' => 'cleanup@test.com',
            'entreprise' => 'Test',
            'pwd' => $hashedPassword,
            'role' => 'user'
        ]);

        // Verify user exists
        $stmt = $db->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->execute(['username' => $testUsername]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($user, "Test user should exist before cleanup");

        // Clean up using helper function
        cleanupTestData($db, 'user', "username = '{$testUsername}'");

        // Verify user was deleted
        $stmt = $db->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->execute(['username' => $testUsername]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertFalse($user, "Test user should be deleted after cleanup");
    }

    public function testDatabaseConstants(): void
    {
        $this->assertTrue(defined('DB_SERVER'), "DB_SERVER constant should be defined");
        $this->assertTrue(defined('DB_NAME'), "DB_NAME constant should be defined");
        $this->assertTrue(defined('DB_USER'), "DB_USER constant should be defined");
        $this->assertTrue(defined('DB_PASSWORD'), "DB_PASSWORD constant should be defined");
        
        $this->assertNotEmpty(DB_SERVER, "DB_SERVER should not be empty");
        $this->assertNotEmpty(DB_NAME, "DB_NAME should not be empty");
        $this->assertNotEmpty(DB_USER, "DB_USER should not be empty");
    }
}
