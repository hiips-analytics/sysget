<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Session;
use App\Models\Course;
use App\Models\Classroom;

class SessionsController extends Controller {

    public function __construct()
    {
        Auth::start();
        if (!Auth::check() || Auth::user()['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Affiche l'emploi du temps complet
     */
    public function index() {
        $sessionModel = new Session();
        $sessions = $sessionModel->getFullCalendar();

        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $timeSlots = array_unique(array_map(fn($session) => $session['start_time'], $sessions));
        sort($timeSlots);

        $schedule = [];
        foreach ($sessions as $session) {
            $schedule[$session['day_of_week']][$session['start_time']] = $session;
        }

        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['success']);

        $this->render('sessions/index', [
            'title' => 'Emploi du Temps Global',
            'days' => $days,
            'timeSlots' => $timeSlots,
            'schedule' => $schedule,
            'success' => $success
        ]);
    }

    /**
     * Affiche le formulaire de planification
     */
    public function create() {
        $courseModel = new Course();
        $roomModel = new Classroom();

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];
        $success = $_SESSION['success'] ?? null;

        // Nettoyer la session
        unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success']);

        $this->render('sessions/create', [
            'title' => 'Planifier un cours',
            'courses' => $courseModel->getWithTeacher(),
            'rooms' => $roomModel->all(),
            'errors' => $errors,
            'old' => $old,
            'success' => $success
        ]);
    }

    /**
     * Enregistre une nouvelle session avec validation
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sessionModel = new Session();
            $courseModel = new Course();

            // 1. Nettoyage des données avec ta méthode clean()
            $courseId    = (int)$this->clean($_POST['course_id']);
            $classroomId = (int)$this->clean($_POST['classroom_id']);
            $day         = $this->clean($_POST['day_of_week']);
            $start       = $this->clean($_POST['start_time']);
            $end         = $this->clean($_POST['end_time']);

            $errors = [];
            $old = [
                'course_id' => $courseId,
                'classroom_id' => $classroomId,
                'day_of_week' => $day,
                'start_time' => $start,
                'end_time' => $end
            ];

            // Validation basique
            if (empty($courseId) || empty($classroomId) || empty($day) || empty($start) || empty($end)) {
                $errors[] = 'Tous les champs sont obligatoires.';
            }

            if (empty($errors)) {
                // 2. Récupérer l'enseignant lié au cours
                $course = $courseModel->find($courseId);
                if (!$course) {
                    $errors[] = 'Cours non trouvé.';
                } else {
                    $teacherId = $course['teacher_id'];

                    // 3. VALIDATION : L'enseignant est-il disponible ?
                    if (!$sessionModel->isWithinTeacherAvailability($teacherId, $day, $start, $end)) {
                        $errors[] = "L'enseignant n'est pas disponible sur ce créneau.";
                    }

                    // 4. VALIDATION : La salle est-elle libre ?
                    if (!$sessionModel->isClassroomFree($classroomId, $day, $start, $end)) {
                        $errors[] = 'La salle est déjà occupée à ce moment-là.';
                    }
                }
            }

            if (!empty($errors)) {
                // Stocker les erreurs et les anciennes valeurs en session
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $old;
                header('Location: /sessions/create');
                exit;
            }

            // 5. INSERTION
            $data = [
                'course_id'    => $courseId,
                'classroom_id' => $classroomId,
                'day'          => $day,
                'start'        => $start,
                'end'          => $end
            ];

            if ($sessionModel->createSession($data)) {
                $_SESSION['success'] = 'Session créée avec succès.';
                header('Location: /sessions');
                exit;
            } else {
                $errors[] = 'Erreur lors de la création de la session.';
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $old;
                header('Location: /sessions/create');
                exit;
            }
        }
    }
}