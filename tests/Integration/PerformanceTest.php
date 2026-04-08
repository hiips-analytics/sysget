<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Classroom;
use App\Models\Session;
use PDO;

class PerformanceTest extends TestCase
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

    /**
     * @group performance
     */
    public function testSessionCreationPerformance()
    {
        // Préparer les données de test
        $this->seedPerformanceTestData();

        $startTime = microtime(true);

        // Créer 100 sessions
        for ($i = 1; $i <= 100; $i++) {
            $sessionData = [
                'course_id' => rand(1, 10), // Cours aléatoire
                'classroom_id' => rand(1, 5), // Salle aléatoire
                'day' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'][rand(0, 4)],
                'start' => sprintf('%02d:00', rand(8, 16)), // Heure aléatoire entre 8h et 16h
                'end' => sprintf('%02d:00', rand(10, 18)) // Heure de fin aléatoire
            ];

            $this->sessionModel->createSession($sessionData);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Vérifier que la création de 100 sessions prend moins de 2 secondes
        $this->assertLessThan(2.0, $executionTime,
            "La création de 100 sessions a pris {$executionTime} secondes, ce qui dépasse la limite de 2 secondes");

        // Vérifier que toutes les sessions ont été créées
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM sessions");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(100, $result['count']);
    }

    /**
     * @group performance
     */
    public function testCalendarRetrievalPerformance()
    {
        // Préparer un grand nombre de sessions
        $this->seedLargeDataset();

        $startTime = microtime(true);

        // Récupérer le planning complet
        $calendar = $this->sessionModel->getFullCalendar();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Vérifier que la récupération prend moins de 0.5 seconde
        $this->assertLessThan(0.5, $executionTime,
            "La récupération du planning a pris {$executionTime} secondes, ce qui dépasse la limite de 0.5 seconde");

        // Vérifier que nous avons récupéré toutes les sessions
        $this->assertGreaterThan(500, count($calendar));
    }

    /**
     * @group performance
     */
    public function testValidationPerformance()
    {
        // Préparer les données de test
        $this->seedPerformanceTestData();

        $startTime = microtime(true);

        // Effectuer 1000 validations de disponibilité
        $validationCount = 0;
        for ($i = 0; $i < 1000; $i++) {
            $teacherId = rand(1, 10);
            $day = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'][rand(0, 4)];
            $start = sprintf('%02d:00', rand(8, 16));
            $end = sprintf('%02d:00', rand(10, 18));

            if ($this->sessionModel->isWithinTeacherAvailability($teacherId, $day, $start, $end)) {
                $validationCount++;
            }
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Vérifier que 1000 validations prennent moins de 1 seconde
        $this->assertLessThan(1.0, $executionTime,
            "1000 validations de disponibilité ont pris {$executionTime} secondes, ce qui dépasse la limite de 1 seconde");

        // Vérifier qu'au moins quelques validations ont réussi
        $this->assertGreaterThan(0, $validationCount);
    }

    /**
     * @group performance
     */
    public function testMemoryUsageDuringBulkOperations()
    {
        $initialMemory = memory_get_usage();

        // Créer un grand nombre de sessions
        $this->seedPerformanceTestData();

        for ($i = 1; $i <= 500; $i++) {
            $sessionData = [
                'course_id' => rand(1, 10),
                'classroom_id' => rand(1, 5),
                'day' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'][rand(0, 4)],
                'start' => sprintf('%02d:00', rand(8, 16)),
                'end' => sprintf('%02d:00', rand(10, 18))
            ];

            $this->sessionModel->createSession($sessionData);
        }

        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;

        // Vérifier que l'utilisation mémoire reste raisonnable (moins de 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed,
            "L'opération a utilisé " . round($memoryUsed / 1024 / 1024, 2) . " MB, ce qui dépasse la limite de 50 MB");

        // Récupérer le planning pour tester la mémoire lors de la lecture
        $calendar = $this->sessionModel->getFullCalendar();

        $afterReadMemory = memory_get_usage();
        $readMemoryUsed = $afterReadMemory - $finalMemory;

        // Vérifier que la lecture n'utilise pas trop de mémoire supplémentaire
        $this->assertLessThan(20 * 1024 * 1024, $readMemoryUsed,
            "La lecture du planning a utilisé " . round($readMemoryUsed / 1024 / 1024, 2) . " MB supplémentaires");
    }

    private function seedPerformanceTestData()
    {
        // Créer 10 enseignants
        for ($i = 1; $i <= 10; $i++) {
            $this->userModel->createUser("Enseignant {$i}", "teacher{$i}@example.com", 'password', 'teacher');
        }

        // Créer 10 cours
        for ($i = 1; $i <= 10; $i++) {
            $this->pdo->exec("INSERT INTO courses (name, teacher_id) VALUES ('Cours {$i}', {$i})");
        }

        // Créer 5 salles
        for ($i = 1; $i <= 5; $i++) {
            $this->pdo->exec("INSERT INTO classrooms (name, capacity) VALUES ('Salle {$i}', " . (20 + $i * 10) . ")");
        }

        // Créer des disponibilités pour chaque enseignant
        for ($teacherId = 1; $teacherId <= 10; $teacherId++) {
            $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
            foreach ($days as $day) {
                $this->pdo->exec("INSERT INTO time_slots (teacher_id, day_of_week, start_time, end_time) VALUES
                    ({$teacherId}, '{$day}', '08:00', '12:00'),
                    ({$teacherId}, '{$day}', '14:00', '18:00')");
            }
        }
    }

    private function seedLargeDataset()
    {
        $this->seedPerformanceTestData();

        // Créer 500 sessions
        for ($i = 1; $i <= 500; $i++) {
            $sessionData = [
                'course_id' => rand(1, 10),
                'classroom_id' => rand(1, 5),
                'day' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'][rand(0, 4)],
                'start' => sprintf('%02d:00', rand(8, 16)),
                'end' => sprintf('%02d:00', rand(10, 18))
            ];

            $this->sessionModel->createSession($sessionData);
        }
    }
}