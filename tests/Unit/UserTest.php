<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use PDO;

class UserTest extends TestCase
{
    private $pdo;
    private $user;

    protected function setUp(): void
    {
        // Créer une base de données SQLite en mémoire pour les tests
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Créer la table users
        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role TEXT NOT NULL CHECK (role IN ('admin', 'teacher', 'student')),
                field_id INTEGER NULL
            )
        ");

        // Injecter la connexion PDO dans le modèle via reflection
        $this->user = new User();
        $reflection = new \ReflectionClass($this->user);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($this->user, $this->pdo);
    }

    public function testFindExistingUser()
    {
        // Insérer un utilisateur de test
        $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Test User', 'test@example.com', 'hashedpass', 'student')");

        $user = $this->user->find(1);

        $this->assertIsArray($user);
        $this->assertEquals('Test User', $user['name']);
        $this->assertEquals('test@example.com', $user['email']);
        $this->assertEquals('student', $user['role']);
    }

    public function testFindNonExistingUser()
    {
        $user = $this->user->find(999);

        $this->assertFalse($user);
    }

    public function testGetStaffReturnsOnlyTeachersAndAdmins()
    {
        // Insérer différents types d'utilisateurs
        $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES
            ('Admin User', 'admin@example.com', 'pass', 'admin'),
            ('Teacher User', 'teacher@example.com', 'pass', 'teacher'),
            ('Student User', 'student@example.com', 'pass', 'student')
        ");

        $staff = $this->user->getStaff();

        $this->assertCount(2, $staff);
        // Vérifier que tous les résultats sont soit admin soit teacher
        foreach ($staff as $user) {
            $this->assertContains($user['role'], ['admin', 'teacher']);
        }
    }

    public function testCreateUser()
    {
        $result = $this->user->createUser(
            'New User',
            'new@example.com',
            'password123',
            'student',
            1
        );

        $this->assertTrue($result);

        // Vérifier que l'utilisateur a été créé
        $createdUser = $this->user->findByEmail('new@example.com');
        $this->assertIsArray($createdUser);
        $this->assertEquals('New User', $createdUser['name']);
        $this->assertEquals('new@example.com', $createdUser['email']);
    }

    public function testFindByEmail()
    {
        $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Test User', 'test@example.com', 'hashedpass', 'student')");

        $user = $this->user->findByEmail('test@example.com');

        $this->assertIsArray($user);
        $this->assertEquals('Test User', $user['name']);
    }

    public function testFindByEmailNonExisting()
    {
        $user = $this->user->findByEmail('nonexisting@example.com');

        $this->assertFalse($user);
    }

    public function testDeleteUser()
    {
        $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Test User', 'test@example.com', 'hashedpass', 'student')");

        $result = $this->user->delete(1);

        $this->assertTrue($result);

        // Vérifier que l'utilisateur a été supprimé
        $user = $this->user->find(1);
        $this->assertFalse($user);
    }
}