<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Session;
use App\Models\User;

class AdminController extends Controller
{
    public function __construct()
    {
        Auth::start();
        Auth::checkAdmin();
    }

    public function index()
    {
        $this->render('admin/dashboard', [
            'title' => 'Tableau de bord Admin'
        ]);
    }

    public function users()
    {
        $userModel = new User();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $this->clean($_POST['name'] ?? '');
            $email = $this->clean($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $this->clean($_POST['role'] ?? 'teacher');

            if (empty($name) || empty($email) || empty($password)) {
                $errors[] = 'Tous les champs doivent être remplis.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Veuillez saisir une adresse email valide.';
            }

            if (!in_array($role, ['admin', 'teacher'], true)) {
                $errors[] = 'Le rôle doit être admin ou teacher.';
            }

            if ($userModel->findByEmail($email)) {
                $errors[] = 'Cet email est déjà utilisé.';
            }

            if (empty($errors)) {
                $userModel->createUser($name, $email, $password, $role, null);
                header('Location: /admin/users');
                exit;
            }
        }

        $users = $userModel->getStaff();

        $this->render('admin/users', [
            'title' => 'Gestion du personnel',
            'users' => $users,
            'errors' => $errors
        ]);
    }

    public function deleteUser($id)
    {
        $userModel = new User();
        $userModel->deleteUser((int)$id);
        header('Location: /admin/users');
        exit;
    }
}
