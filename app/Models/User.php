<?php
namespace App\Models;

class User extends Model
{
    protected $table = 'users';

    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function createUser(string $name, string $email, string $password, string $role, ?int $fieldId = null)
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, email, password, role, field_id) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role,
            $fieldId
        ]);
    }

    public function getTeachers()
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'teacher'");
        return $stmt->fetchAll();
    }

    public function getStaff()
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role IN ('admin', 'teacher') ORDER BY role DESC, name ASC");
        return $stmt->fetchAll();
    }

    public function deleteUser($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getStudentsByField($fieldId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = 'student' AND field_id = ?");
        $stmt->execute([$fieldId]);
        return $stmt->fetchAll();
    }

    public function updateUser($id, $name, $email, $role)
    {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $role, $id]);
    }
}
