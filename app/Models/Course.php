<?php
namespace App\Models;

class Course extends Model
{
    protected $table = 'courses';

    public function getWithTeacher() {
        $sql = "SELECT c.*, u.name as teacher_name 
                FROM courses c 
                JOIN users u ON c.teacher_id = u.id";
        return $this->db->query($sql)->fetchAll();
    }

    public function updateCourse($id, $name, $teacherId) {
        $stmt = $this->db->prepare("UPDATE courses SET name = ?, teacher_id = ? WHERE id = ?");
        return $stmt->execute([$name, $teacherId, $id]);
    }
}