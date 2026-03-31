<?php
namespace App\Models;

class TimeSlot extends Model
{
    protected $table = 'time_slots';

    public function getByTeacher($teacherId) {
        $stmt = $this->db->prepare("SELECT * FROM time_slots WHERE teacher_id = ? ORDER BY day_of_week, start_time");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }
}