<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Controllers\SessionsController;
use App\Models\Session;
use App\Models\Course;
use App\Models\Classroom;
use App\Models\User;
use PDO;

class SessionsControllerTest extends TestCase
{
    private $pdo;
    private $controller;
    private $session;
    private $course;
    private $classroom;
    private $user;

    protected function setUp(): void
    {
        // Démarrer la session pour les tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Inclure les fichiers nécessaires manuellement
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../../app/Core/Database.php';
        require_once __DIR__ . '/../../app/Models/Model.php';
        require_once __DIR__ . '/../../app/Models/Session.php';
        require_once __DIR__ . '/../../app/Models/Course.php';
        require_once __DIR__ . '/../../app/Models/Classroom.php';
        require_once __DIR__ . '/../../app/Models/User.php';
        require_once __DIR__ . '/../../app/Controllers/Controller.php';
        require_once __DIR__ . '/../../app/Controllers/SessionsController.php';

        // Créer une base de données SQLite en mémoire pour les tests
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Créer toutes les tables nécessaires
        $this->createTables();

        // Injecter la connexion PDO dans tous les modèles
        $this->injectPdoIntoModel($this->session = new \App\Models\Session());
        $this->injectPdoIntoModel($this->course = new \App\Models\Course());
        $this->injectPdoIntoModel($this->classroom = new \App\Models\Classroom());
        $this->injectPdoIntoModel($this->user = new \App\Models\User());

        // Créer le contrôleur
        $this->controller = new \App\Controllers\SessionsController();

        // Simuler un utilisateur admin connecté
        $_SESSION['user'] = [
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin'
        ];
    }

    private function createTables()
    {
        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL,
                field_id INTEGER NULL
            )
        ");

        $this->pdo->exec("
            CREATE TABLE courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                teacher_id INTEGER NOT NULL
            )
        ");

        $this->pdo->exec("
            CREATE TABLE classrooms (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(50) NOT NULL,
                capacity INTEGER
            )
        ");

        $this->pdo->exec("
            CREATE TABLE time_slots (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                teacher_id INTEGER NOT NULL,
                day_of_week VARCHAR(20) NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL
            )
        ");

        $this->pdo->exec("
            CREATE TABLE sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                course_id INTEGER NOT NULL,
                classroom_id INTEGER NOT NULL,
                day_of_week VARCHAR(20) NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL
            )
        ");
    }

    private function injectPdoIntoModel($model)
    {
        $reflection = new \ReflectionClass($model);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($model, $this->pdo);
    }

    protected function tearDown(): void
    {
        // Nettoyer la session
        if (isset($_SESSION)) {
            unset($_SESSION['user'], $_SESSION['errors'], $_SESSION['old'], $_SESSION['success']);
        }
    }

    public function testCreateActionRendersFormWithData()
    {
        // Insérer des données de test
        $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Teacher 1', 'teacher@example.com', 'pass', 'teacher')");
        $this->pdo->exec("INSERT INTO courses (name, teacher_id) VALUES ('Mathématiques', 1)");
        $this->pdo->exec("INSERT INTO classrooms (name, capacity) VALUES ('Salle 101', 30)");

        // Capturer la sortie du contrôleur
        ob_start();
        $this->controller->create();
        $output = ob_get_clean();

        // Vérifier que le formulaire est rendu
        $this->assertStringContainsString('Planifier un cours', $output);
        $this->assertStringContainsString('Mathématiques', $output);
        $this->assertStringContainsString('Salle 101', $output);
    }

    public function testStoreActionCreatesSessionSuccessfully()
    {
        // Insérer des données de test
        $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Teacher 1', 'teacher@example.com', 'pass', 'teacher')");
        $this->pdo->exec("INSERT INTO courses (name, teacher_id) VALUES ('Mathématiques', 1)");
        $this->pdo->exec("INSERT INTO classrooms (name, capacity) VALUES ('Salle 101', 30)");
        $this->pdo->exec("INSERT INTO time_slots (teacher_id, day_of_week, start_time, end_time) VALUES (1, 'Lundi', '08:00', '12:00')");

        // Simuler une requête POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'course_id' => '1',
            'classroom_id' => '1',
            'day_of_week' => 'Lundi',
            'start_time' => '09:00',
            'end_time' => '11:00'
        ];

        // Capturer les headers de redirection
        $headers = [];
        $this->controller = $this->getMockBuilder(SessionsController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        $this->controller->expects($this->once())
            ->method('redirect')
            ->with('/sessions');

        // Injecter la connexion PDO
        $this->injectPdoIntoModel($this->controller);

        // Exécuter l'action store
        $this->controller->store();

        // Vérifier que la session a été créée
        $stmt = $this->pdo->query("SELECT * FROM sessions");
        $sessions = $stmt->fetchAll();
        $this->assertCount(1, $sessions);
        $this->assertEquals('Lundi', $sessions[0]['day_of_week']);
    }

    public function testStoreActionFailsWithValidationErrors()
    {
        // Simuler une requête POST sans données
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];

        // Capturer les headers de redirection
        $this->controller = $this->getMockBuilder(SessionsController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        $this->controller->expects($this->once())
            ->method('redirect')
            ->with('/sessions/create');

        // Injecter la connexion PDO
        $this->injectPdoIntoModel($this->controller);

        // Exécuter l'action store
        $this->controller->store();

        // Vérifier que des erreurs ont été définies en session
        $this->assertArrayHasKey('errors', $_SESSION);
        $this->assertContains('Tous les champs sont obligatoires.', $_SESSION['errors']);
    }

    public function testStoreActionFailsWhenTeacherNotAvailable()
    {
        // Insérer des données de test sans time_slot
        $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Teacher 1', 'teacher@example.com', 'pass', 'teacher')");
        $this->pdo->exec("INSERT INTO courses (name, teacher_id) VALUES ('Mathématiques', 1)");
        $this->pdo->exec("INSERT INTO classrooms (name, capacity) VALUES ('Salle 101', 30)");

        // Simuler une requête POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'course_id' => '1',
            'classroom_id' => '1',
            'day_of_week' => 'Lundi',
            'start_time' => '09:00',
            'end_time' => '11:00'
        ];

        // Capturer les headers de redirection
        $this->controller = $this->getMockBuilder(SessionsController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        $this->controller->expects($this->once())
            ->method('redirect')
            ->with('/sessions/create');

        // Injecter la connexion PDO
        $this->injectPdoIntoModel($this->controller);

        // Exécuter l'action store
        $this->controller->store();

        // Vérifier que des erreurs ont été définies en session
        $this->assertArrayHasKey('errors', $_SESSION);
        $this->assertContains("L'enseignant n'est pas disponible sur ce créneau.", $_SESSION['errors']);
    }

    public function testStoreActionFailsWhenClassroomOccupied()
    {
        // Insérer des données de test avec une session existante
        $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Teacher 1', 'teacher@example.com', 'pass', 'teacher')");
        $this->pdo->exec("INSERT INTO courses (name, teacher_id) VALUES ('Mathématiques', 1)");
        $this->pdo->exec("INSERT INTO classrooms (name, capacity) VALUES ('Salle 101', 30)");
        $this->pdo->exec("INSERT INTO time_slots (teacher_id, day_of_week, start_time, end_time) VALUES (1, 'Lundi', '08:00', '12:00')");
        $this->pdo->exec("INSERT INTO sessions (course_id, classroom_id, day_of_week, start_time, end_time) VALUES (1, 1, 'Lundi', '08:00', '12:00')");

        // Simuler une requête POST qui chevauche
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'course_id' => '1',
            'classroom_id' => '1',
            'day_of_week' => 'Lundi',
            'start_time' => '10:00',
            'end_time' => '14:00'
        ];

        // Capturer les headers de redirection
        $this->controller = $this->getMockBuilder(SessionsController::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        $this->controller->expects($this->once())
            ->method('redirect')
            ->with('/sessions/create');

        // Injecter la connexion PDO
        $this->injectPdoIntoModel($this->controller);

        // Exécuter l'action store
        $this->controller->store();

        // Vérifier que des erreurs ont été définies en session
        $this->assertArrayHasKey('errors', $_SESSION);
        $this->assertContains('La salle est déjà occupée à ce moment-là.', $_SESSION['errors']);
    }
}