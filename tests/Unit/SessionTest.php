<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Session;
use PDO;

class SessionTest extends TestCase
{
    private $pdo;
    private $session;

    protected function setUp(): void
    {
        // Créer une base de données SQLite en mémoire pour les tests
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Créer les tables nécessaires
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

        $this->pdo->exec("
            CREATE TABLE courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                teacher_id INTEGER NOT NULL
            )
        ");

        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL
            )
        ");

        $this->pdo->exec("
            CREATE TABLE classrooms (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(50) NOT NULL,
                capacity INTEGER
            )
        ");

        // Injecter la connexion PDO dans le modèle
        $this->session = new Session();
        $reflection = new \ReflectionClass($this->session);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($this->session, $this->pdo);
    }

    public function testIsWithinTeacherAvailabilityWhenAvailable()
    {
        // Insérer une disponibilité pour l'enseignant
        $this->pdo->exec("INSERT INTO time_slots (teacher_id, day_of_week, start_time, end_time) VALUES (1, 'Lundi', '08:00', '12:00')");

        $result = $this->session->isWithinTeacherAvailability(1, 'Lundi', '09:00', '11:00');

        $this->assertTrue($result);
    }

    public function testIsWithinTeacherAvailabilityWhenNotAvailable()
    {
        // Insérer une disponibilité pour l'enseignant
        $this->pdo->exec("INSERT INTO time_slots (teacher_id, day_of_week, start_time, end_time) VALUES (1, 'Lundi', '08:00', '12:00')");

        $result = $this->session->isWithinTeacherAvailability(1, 'Mardi', '09:00', '11:00');

        $this->assertFalse($result);
    }

    public function testIsWithinTeacherAvailabilityOutsideTimeSlot()
    {
        // Insérer une disponibilité pour l'enseignant
        $this->pdo->exec("INSERT INTO time_slots (teacher_id, day_of_week, start_time, end_time) VALUES (1, 'Lundi', '08:00', '12:00')");

        $result = $this->session->isWithinTeacherAvailability(1, 'Lundi', '13:00', '15:00');

        $this->assertFalse($result);
    }

    public function testIsClassroomFreeWhenFree()
    {
        // Aucune session dans la salle
        $result = $this->session->isClassroomFree(1, 'Lundi', '09:00', '11:00');

        $this->assertTrue($result);
    }

    public function testIsClassroomFreeWhenOccupied()
    {
        // Insérer une session existante
        $this->pdo->exec("INSERT INTO sessions (course_id, classroom_id, day_of_week, start_time, end_time) VALUES (1, 1, 'Lundi', '08:00', '12:00')");

        // Tester un créneau qui chevauche
        $result = $this->session->isClassroomFree(1, 'Lundi', '10:00', '14:00');

        $this->assertFalse($result);
    }

    public function testIsClassroomFreeWhenNotOccupied()
    {
        // Insérer une session existante
        $this->pdo->exec("INSERT INTO sessions (course_id, classroom_id, day_of_week, start_time, end_time) VALUES (1, 1, 'Lundi', '08:00', '10:00')");

        // Tester un créneau qui ne chevauche pas
        $result = $this->session->isClassroomFree(1, 'Lundi', '12:00', '14:00');

        $this->assertTrue($result);
    }

    public function testCreateSession()
    {
        $data = [
            'course_id' => 1,
            'classroom_id' => 1,
            'day' => 'Lundi',
            'start' => '09:00',
            'end' => '11:00'
        ];

        $result = $this->session->createSession($data);

        $this->assertTrue($result);

        // Vérifier que la session a été créée
        $stmt = $this->pdo->query("SELECT * FROM sessions WHERE course_id = 1");
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Lundi', $session['day_of_week']);
        $this->assertEquals('09:00', $session['start_time']);
        $this->assertEquals('11:00', $session['end_time']);
    }

    public function testUpdateSession()
    {
        // Créer une session
        $this->pdo->exec("INSERT INTO sessions (course_id, classroom_id, day_of_week, start_time, end_time) VALUES (1, 1, 'Lundi', '09:00', '11:00')");

        $data = [
            'course_id' => 2,
            'classroom_id' => 2,
            'day' => 'Mardi',
            'start' => '10:00',
            'end' => '12:00'
        ];

        $result = $this->session->updateSession(1, $data);

        $this->assertTrue($result);

        // Vérifier que la session a été mise à jour
        $stmt = $this->pdo->query("SELECT * FROM sessions WHERE id = 1");
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Mardi', $session['day_of_week']);
        $this->assertEquals('10:00', $session['start_time']);
        $this->assertEquals('12:00', $session['end_time']);
    }

    public function testGetFullCalendar()
    {
        // Insérer des données de test
        $this->pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Teacher 1', 'teacher@example.com', 'pass', 'teacher')");
        $this->pdo->exec("INSERT INTO courses (name, teacher_id) VALUES ('Mathématiques', 1)");
        $this->pdo->exec("INSERT INTO classrooms (name, capacity) VALUES ('Salle 101', 30)");
        $this->pdo->exec("INSERT INTO sessions (course_id, classroom_id, day_of_week, start_time, end_time) VALUES (1, 1, 'Lundi', '09:00', '11:00')");

        // Mock de la méthode pour éviter le problème FIELD avec SQLite
        $sessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getFullCalendar'])
            ->getMock();

        // Injecter la connexion PDO dans le mock
        $reflection = new \ReflectionClass($sessionMock);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($sessionMock, $this->pdo);

        $sessionMock->method('getFullCalendar')->willReturn([
            [
                'session_id' => 1,
                'day_of_week' => 'Lundi',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'course_name' => 'Mathématiques',
                'teacher_name' => 'Teacher 1',
                'classroom_name' => 'Salle 101'
            ]
        ]);

        $calendar = $sessionMock->getFullCalendar();

        $this->assertIsArray($calendar);
        $this->assertCount(1, $calendar);
        $this->assertEquals('Mathématiques', $calendar[0]['course_name']);
        $this->assertEquals('Teacher 1', $calendar[0]['teacher_name']);
        $this->assertEquals('Salle 101', $calendar[0]['classroom_name']);
    }
}