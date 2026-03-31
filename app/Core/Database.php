<?php
namespace App\Core;

use PDO;
use PDOException;

class Database 
{
    private static $instance = null;
    private $connect;
    private $host_name  = 'localhost';
    private $db_name    = 'sysget_db';
    private $user_name  = 'root';
    private $password   = 'root';

    private function __construct() 
    {
        try {
            $this->connect = new PDO(
                "mysql:host={$this->host_name};dbname={$this->db_name};charset=utf8",
                $this->user_name,
                $this->password, 
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public static function getConnection() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connect;
    }
}