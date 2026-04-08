<?php
namespace App\Core;

use App\Models\User;

class Auth
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function attempt(string $email, string $password): bool
    {
        self::start();
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            return true;
        }

        return false;
    }

    public static function logout()
    {
        self::start();
        unset($_SESSION['user']);
        session_destroy();
    }

    public static function isAdmin(): bool
    {
        return self::check() && self::user()['role'] === 'admin';
    }

    public static function checkAdmin()
    {
        if (!self::isAdmin()) {
            header('Location: /login');
            exit;
        }
    }
}
