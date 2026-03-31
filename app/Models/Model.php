<?php
namespace App\Models;

use App\Core\Database;
use PDO;

abstract class Model 
{
    protected $db;
    protected $table;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all()
    {
        $query = $this->db->query("SELECT * FROM {$this->table}");
        return $query->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}