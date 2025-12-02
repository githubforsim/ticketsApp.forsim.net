<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../ticketsApp/app/src/Models/UserModel.php';

/**
 * Tests for UserModel class
 */
class UserModelTest extends TestCase
{
    private PDO $db;
    private UserModel $userModel;
    private string $testUsername = 'test_user_phpunit';

    protected function setUp(): void
    {
        $this->db = getTestDbConnection();
        $this->userModel = new UserModel($this->db);
        
        // Clean up any existing test data
        cleanupTestData($this->db, 'user_produit', "username = '{$this->testUsername}'");
        cleanupTestData($this->db, 'user', "username = '{$this->testUsername}'");
    }

    protected function tearDown(): void
    {
        // Clean up test data after each test
        cleanupTestData($this->db, 'user_produit', "username = '{$this->testUsername}'");
        cleanupTestData($this->db, 'user', "username = '{$this->testUsername}'");
    }

    public function testGetUserByUsername(): void
    {
        // Insert test user
        $hashedPassword = password_hash('test123', PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO user (username, mail, entreprise, pwd, role) 
             VALUES (:username, :mail, :entreprise, :pwd, :role)"
        );
        $stmt->execute([
            'username' => $this->testUsername,
            'mail' => 'test@example.com',
            'entreprise' => 'Test Corp',
            'pwd' => $hashedPassword,
            'role' => 'user'
        ]);

        // Test getUserByUsername
        $user = $this->userModel->getUserByUsername($this->testUsername);

        $this->assertNotNull($user, "User should be found");
        $this->assertEquals($this->testUsername, $user['username']);
        $this->assertEquals('test@example.com', $user['mail']);
        $this->assertEquals('Test Corp', $user['entreprise']);
        $this->assertEquals('user', $user['role']);
    }

    public function testGetUserByUsernameNotFound(): void
    {
        $user = $this->userModel->getUserByUsername('nonexistent_user_12345');
        $this->assertFalse($user, "Non-existent user should return false");
    }

    public function testGetUserRole(): void
    {
        // Insert test user with admin role
        $hashedPassword = password_hash('test123', PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO user (username, mail, entreprise, pwd, role) 
             VALUES (:username, :mail, :entreprise, :pwd, :role)"
        );
        $stmt->execute([
            'username' => $this->testUsername,
            'mail' => 'admin@example.com',
            'entreprise' => 'Test Corp',
            'pwd' => $hashedPassword,
            'role' => 'admin'
        ]);

        $role = $this->userModel->getUserRole($this->testUsername);
        
        $this->assertNotEmpty($role, "Role should be found");
        // getUserRole returns string directly, not array
        $this->assertEquals('admin', $role);
    }

    public function testIsUsernameExists(): void
    {
        // Insert test user
        $hashedPassword = password_hash('test123', PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO user (username, mail, entreprise, pwd, role) 
             VALUES (:username, :mail, :entreprise, :pwd, :role)"
        );
        $stmt->execute([
            'username' => $this->testUsername,
            'mail' => 'test@example.com',
            'entreprise' => 'Test Corp',
            'pwd' => $hashedPassword,
            'role' => 'user'
        ]);

        // Test existing username
        $exists = $this->userModel->isUsernameExists($this->testUsername);
        $this->assertTrue($exists, "Username should exist");

        // Test non-existing username
        $notExists = $this->userModel->isUsernameExists('nonexistent_user_xyz');
        $this->assertFalse($notExists, "Non-existent username should return false");
    }

    public function testPasswordVerification(): void
    {
        // Insert test user with known password
        $password = 'SecurePass123!';
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare(
            "INSERT INTO user (username, mail, entreprise, pwd, role) 
             VALUES (:username, :mail, :entreprise, :pwd, :role)"
        );
        $stmt->execute([
            'username' => $this->testUsername,
            'mail' => 'test@example.com',
            'entreprise' => 'Test Corp',
            'pwd' => $hashedPassword,
            'role' => 'user'
        ]);

        $user = $this->userModel->getUserByUsername($this->testUsername);
        
        // Verify password matches
        $this->assertTrue(
            password_verify($password, $user['pwd']),
            "Password verification should succeed"
        );
        
        // Verify wrong password fails
        $this->assertFalse(
            password_verify('WrongPassword', $user['pwd']),
            "Wrong password should fail verification"
        );
    }
}
