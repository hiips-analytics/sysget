<?php
namespace App\Models;

class Classroom extends Model
{
    protected $table = 'classrooms';

    public function createClassroom($name, $capacity) {
        $stmt = $this->db->prepare("INSERT INTO classrooms (name, capacity) VALUES (?, ?)");
        return $stmt->execute([$name, $capacity]);
    }

    public function updateClassroom($id, $name, $capacity) {
        $stmt = $this->db->prepare("UPDATE classrooms SET name = ?, capacity = ? WHERE id = ?");
        return $stmt->execute([$name, $capacity, $id]);
    }
}