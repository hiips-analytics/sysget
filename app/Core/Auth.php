<?php
namespace App\Core;

class Auth
{
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isAdmin() {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
    }

    public static function checkAdmin() {
        if (!self::isAdmin()) {
            header('Location: /login');
            exit;
        }
    }
}