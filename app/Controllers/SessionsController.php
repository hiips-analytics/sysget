<?php
namespace App\Controllers;

use App\Models\Session;
use App\Models\Course;
use App\Models\Classroom;

class SessionsController extends Controller {

    /**
     * Affiche l'emploi du temps complet
     */
    public function index() {
        $sessionModel = new Session();
        $sessions = $sessionModel->getFullCalendar();

        $this->render('sessions/index', [
            'title' => 'Emploi du Temps Global',
            'sessions' => $sessions
        ]);
    }

    /**
     * Affiche le formulaire de planification
     */
    public function create() {
        $courseModel = new Course();
        $roomModel = new Classroom();

        $this->render('sessions/create', [
            'title' => 'Planifier un cours',
            'courses' => $courseModel->getWithTeacher(),
            'rooms' => $roomModel->all()
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

            // 2. Récupérer l'enseignant lié au cours
            $course = $courseModel->find($courseId);
            $teacherId = $course['teacher_id'];

            // 3. VALIDATION : L'enseignant est-il disponible ?
            if (!$sessionModel->isWithinTeacherAvailability($teacherId, $day, $start, $end)) {
                die("Erreur : L'enseignant n'est pas disponible sur ce créneau (TimeSlot manquant).");
            }

            // 4. VALIDATION : La salle est-elle libre ?
            if (!$sessionModel->isClassroomFree($classroomId, $day, $start, $end)) {
                die("Erreur : La salle est déjà occupée à ce moment-là.");
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
                header('Location: /session/index');
                exit;
            }
        }
    }
}