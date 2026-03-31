<?php
namespace App\Models;

class Field extends Model
{
    protected $table = 'fields';

    public function createField($name) {
        $stmt = $this->db->prepare("INSERT INTO fields (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function updateField($id, $name) {
        $stmt = $this->db->prepare("UPDATE fields SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }
}