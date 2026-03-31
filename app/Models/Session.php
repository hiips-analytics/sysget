<?php
namespace App\Models;

class Session extends Model
{
    protected $table = 'sessions';

    /**
     * Vérifie si l'horaire de la session est inclus dans une disponibilité de l'enseignant
     */
    public function isWithinTeacherAvailability($teacherId, $day, $start, $end) {
        $sql = "SELECT COUNT(*) FROM time_slots 
                WHERE teacher_id = :tid 
                AND day_of_week = :day 
                AND start_time <= :start 
                AND end_time >= :end";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'tid'   => $teacherId,
            'day'   => $day,
            'start' => $start,
            'end'   => $end
        ]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Vérifie si la salle est libre sur ce créneau
     */
    public function isClassroomFree($classroomId, $day, $start, $end) {
        $sql = "SELECT COUNT(*) FROM sessions 
                WHERE classroom_id = :cid 
                AND day_of_week = :day 
                AND NOT (end_time <= :start OR start_time >= :end)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'cid'   => $classroomId,
            'day'   => $day,
            'start' => $start,
            'end'   => $end
        ]);
        
        return $stmt->fetchColumn() == 0;
    }

    public function createSession($data) {
        $sql = "INSERT INTO sessions (course_id, classroom_id, day_of_week, start_time, end_time) 
                VALUES (:course_id, :classroom_id, :day, :start, :end)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateSession($id, $data) {
        $sql = "UPDATE sessions SET 
                course_id = :course_id, 
                classroom_id = :classroom_id, 
                day_of_week = :day, 
                start_time = :start, 
                end_time = :end 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function getFullCalendar() {
    $sql = "SELECT 
                s.id as session_id,
                s.day_of_week,
                s.start_time,
                s.end_time,
                c.name as course_name,
                u.name as teacher_name,
                cl.name as classroom_name
            FROM sessions s
            JOIN courses c ON s.course_id = c.id
            JOIN users u ON c.teacher_id = u.id
            JOIN classrooms cl ON s.classroom_id = cl.id
            ORDER BY 
                FIELD(s.day_of_week, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'),
                s.start_time ASC";

    $stmt = $this->db->query($sql);
    return $stmt->fetchAll();
}
}