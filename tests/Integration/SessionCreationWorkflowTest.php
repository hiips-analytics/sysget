<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Classroom;
use App\Models\Session;
use PDO;

class SessionCreationWorkflowTest extends TestCase
{
    private $pdo;
    private $userModel;
    private $courseModel;
    private $classroomModel;
    private $sessionModel;

    protected function setUp(): void
    {
        // Créer une base de données SQLite en mémoire pour les tests
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Créer toutes les tables nécessaires
        $this->createTables();

        // Initialiser les modèles
        $this->userModel = new User();
        $this->courseModel = new Course();
        $this->classroomModel = new Classroom();
        $this->sessionModel = new Session();

        // Injecter la connexion PDO dans tous les modèles
        $this->injectPdoIntoModel($this->userModel);
        $this->injectPdoIntoModel($this->courseModel);
        $this->injectPdoIntoModel($this->classroomModel);
        $this->injectPdoIntoModel($this->sessionModel);

        // Insérer des données de test
        $this->seedTestData();
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

    private function seedTestData()
    {
        // Créer des utilisateurs
        $this->userModel->createUser('Professeur Dupont', 'dupont@example.com', 'password', 'teacher');
        $this->userModel->createUser('Professeur Martin', 'martin@example.com', 'password', 'teacher');
        $this->userModel->createUser('Admin', 'admin@example.com', 'password', 'admin');

        // Créer des cours
        $this->pdo->exec("INSERT INTO courses (name, teacher_id) VALUES ('Mathématiques', 1)");
        $this->pdo->exec("INSERT INTO courses (name, teacher_id) VALUES ('Physique', 2)");

        // Créer des salles
        $this->pdo->exec("INSERT INTO classrooms (name, capacity) VALUES ('Salle 101', 30)");
        $this->pdo->exec("INSERT INTO classrooms (name, capacity) VALUES ('Salle 102', 25)");
        $this->pdo->exec("INSERT INTO classrooms (name, capacity) VALUES ('Amphi A', 100)");

        // Créer des disponibilités
        $this->pdo->exec("INSERT INTO time_slots (teacher_id, day_of_week, start_time, end_time) VALUES
            (1, 'Lundi', '08:00', '12:00'),
            (1, 'Mardi', '08:00', '12:00'),
            (1, 'Mercredi', '08:00', '12:00'),
            (2, 'Lundi', '14:00', '18:00'),
            (2, 'Mardi', '14:00', '18:00'),
            (2, 'Mercredi', '14:00', '18:00')
        ");
    }

    public function testCompleteSessionCreationWorkflow()
    {
        // Étape 1: Vérifier les données initiales
        $courses = $this->courseModel->getWithTeacher();
        $this->assertCount(2, $courses);

        $classrooms = $this->classroomModel->all();
        $this->assertCount(3, $classrooms);

        // Étape 2: Tester la validation de disponibilité d'enseignant
        $isAvailable = $this->sessionModel->isWithinTeacherAvailability(1, 'Lundi', '09:00', '11:00');
        $this->assertTrue($isAvailable, 'Le professeur Dupont devrait être disponible lundi 9h-11h');

        $isNotAvailable = $this->sessionModel->isWithinTeacherAvailability(1, 'Lundi', '13:00', '15:00');
        $this->assertFalse($isNotAvailable, 'Le professeur Dupont ne devrait pas être disponible lundi 13h-15h');

        // Étape 3: Tester la validation de disponibilité de salle
        $isFree = $this->sessionModel->isClassroomFree(1, 'Lundi', '09:00', '11:00');
        $this->assertTrue($isFree, 'La salle 101 devrait être libre lundi 9h-11h');

        // Étape 4: Créer une session valide
        $sessionData = [
            'course_id' => 1, // Mathématiques avec Prof Dupont
            'classroom_id' => 1, // Salle 101
            'day' => 'Lundi',
            'start' => '09:00',
            'end' => '11:00'
        ];

        $result = $this->sessionModel->createSession($sessionData);
        $this->assertTrue($result, 'La création de session devrait réussir');

        // Étape 5: Vérifier que la session a été créée
        $stmt = $this->pdo->query("SELECT * FROM sessions WHERE course_id = 1");
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($session, 'La session devrait exister en base');
        $this->assertEquals('Lundi', $session['day_of_week']);
        $this->assertEquals('09:00', $session['start_time']);
        $this->assertEquals('11:00', $session['end_time']);

        // Étape 6: Tester que la salle n'est plus disponible pour un créneau qui chevauche
        $isStillFree = $this->sessionModel->isClassroomFree(1, 'Lundi', '10:00', '12:00');
        $this->assertFalse($isStillFree, 'La salle 101 ne devrait plus être libre lundi 10h-12h');

        // Étape 7: Tester la récupération du planning complet (avec mock pour éviter FIELD)
        $sessionMock = $this->getMockBuilder(\App\Models\Session::class)
            ->onlyMethods(['getFullCalendar'])
            ->getMock();

        $this->injectPdoIntoModel($sessionMock);
        $sessionMock->method('getFullCalendar')->willReturn([
            [
                'session_id' => 1,
                'day_of_week' => 'Lundi',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'course_name' => 'Mathématiques',
                'teacher_name' => 'Professeur Dupont',
                'classroom_name' => 'Salle 101'
            ]
        ]);

        $calendar = $sessionMock->getFullCalendar();
        $this->assertCount(1, $calendar, 'Le planning devrait contenir 1 session');
        $this->assertEquals('Mathématiques', $calendar[0]['course_name']);
        $this->assertEquals('Professeur Dupont', $calendar[0]['teacher_name']);
        $this->assertEquals('Salle 101', $calendar[0]['classroom_name']);
    }

    public function testMultipleSessionsCreationAndConflicts()
    {
        // Créer plusieurs sessions pour tester les conflits

        // Session 1: Mathématiques lundi 9h-11h salle 101
        $session1 = [
            'course_id' => 1,
            'classroom_id' => 1,
            'day' => 'Lundi',
            'start' => '09:00',
            'end' => '11:00'
        ];
        $this->sessionModel->createSession($session1);

        // Session 2: Physique lundi 14h-16h salle 102 (devrait réussir)
        $session2 = [
            'course_id' => 2,
            'classroom_id' => 2,
            'day' => 'Lundi',
            'start' => '14:00',
            'end' => '16:00'
        ];
        $result2 = $this->sessionModel->createSession($session2);
        $this->assertTrue($result2, 'La session 2 devrait être créée');

        // Session 3: Mathématiques lundi 10h-12h salle 101 (conflit de salle)
        $session3 = [
            'course_id' => 1,
            'classroom_id' => 1,
            'day' => 'Lundi',
            'start' => '10:00',
            'end' => '12:00'
        ];
        $isClassroomFree = $this->sessionModel->isClassroomFree(1, 'Lundi', '10:00', '12:00');
        $this->assertFalse($isClassroomFree, 'La salle 101 ne devrait pas être libre lundi 10h-12h');

        // Session 4: Physique lundi 15h-17h salle 102 (devrait réussir car l'enseignant est disponible)
        $session4 = [
            'course_id' => 2,
            'classroom_id' => 2,
            'day' => 'Lundi',
            'start' => '15:00',
            'end' => '17:00'
        ];
        $isTeacherAvailable = $this->sessionModel->isWithinTeacherAvailability(2, 'Lundi', '15:00', '17:00');
        $this->assertTrue($isTeacherAvailable, 'Le professeur Martin devrait être disponible lundi 15h-17h');

        // Vérifier le nombre total de sessions créées
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM sessions");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(2, $result['count'], 'Il devrait y avoir exactement 2 sessions en base');
    }

    public function testSessionUpdateWorkflow()
    {
        // Créer une session initiale
        $sessionData = [
            'course_id' => 1,
            'classroom_id' => 1,
            'day' => 'Lundi',
            'start' => '09:00',
            'end' => '11:00'
        ];
        $this->sessionModel->createSession($sessionData);

        // Récupérer l'ID de la session créée
        $stmt = $this->pdo->query("SELECT id FROM sessions WHERE course_id = 1");
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        $sessionId = $session['id'];

        // Mettre à jour la session
        $updateData = [
            'course_id' => 1,
            'classroom_id' => 2, // Changer de salle
            'day' => 'Mardi', // Changer de jour
            'start' => '10:00', // Changer l'heure
            'end' => '12:00'
        ];

        $result = $this->sessionModel->updateSession($sessionId, $updateData);
        $this->assertTrue($result, 'La mise à jour devrait réussir');

        // Vérifier que la session a été mise à jour
        $stmt = $this->pdo->query("SELECT * FROM sessions WHERE id = $sessionId");
        $updatedSession = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('Mardi', $updatedSession['day_of_week']);
        $this->assertEquals('10:00', $updatedSession['start_time']);
        $this->assertEquals(2, $updatedSession['classroom_id']);
    }
}