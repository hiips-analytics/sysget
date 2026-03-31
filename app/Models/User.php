<?php
namespace App\Models;

class User extends Model
{
    protected $table = 'users';

    public function getTeachers() {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'teacher'");
        return $stmt->fetchAll();
    }

    public function getStudentsByField($fieldId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = 'student' AND field_id = ?");
        $stmt->execute([$fieldId]);
        return $stmt->fetchAll();
    }

    public function updateUser($id, $name, $email, $role) {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $role, $id]);
    }
}