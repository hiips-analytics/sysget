<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Field;
use App\Models\User;

class AuthController extends Controller
{
    public function login()
    {
        Auth::start();

        if (Auth::check()) {
            header('Location: /sessions');
            exit;
        }

        $errors = [];
        $success = isset($_GET['registered']) ? 'Votre compte a bien été créé. Vous pouvez maintenant vous connecter.' : null;
        $old = [
            'email' => $_POST['email'] ?? ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->clean($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $errors[] = 'Merci de renseigner votre email et votre mot de passe.';
            } elseif (!Auth::attempt($email, $password)) {
                $errors[] = 'Email ou mot de passe incorrect.';
            } else {
                header('Location: /sessions');
                exit;
            }

            $old['email'] = $email;
        }

        $this->render('auth/login', [
            'title' => 'Connexion',
            'errors' => $errors,
            'success' => $success,
            'old' => $old
        ]);
    }

    public function register()
    {
        Auth::start();

        if (Auth::check()) {
            header('Location: /sessions');
            exit;
        }

        $fieldModel = new Field();
        $fields = $fieldModel->all();
        $errors = [];
        $old = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? 'student',
            'field_id' => $_POST['field_id'] ?? ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $this->clean($_POST['name'] ?? '');
            $email = $this->clean($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['password_confirm'] ?? '';
            $role = $this->clean($_POST['role'] ?? 'student');
            $fieldId = !empty($_POST['field_id']) ? (int)$_POST['field_id'] : null;

            if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
                $errors[] = 'Tous les champs obligatoires doivent être complétés.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email invalide.';
            }

            if ($password !== $confirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if (!in_array($role, ['admin', 'teacher', 'student'])) {
                $errors[] = 'Rôle invalide.';
            }

            if ($role === 'student' && empty($fieldId)) {
                $errors[] = 'Veuillez sélectionner une filière pour un étudiant.';
            }

            $userModel = new User();
            if ($userModel->findByEmail($email)) {
                $errors[] = 'Un utilisateur existe déjà avec cet email.';
            }

            if (empty($errors)) {
                if ($userModel->createUser($name, $email, $password, $role, $fieldId)) {
                    header('Location: /login?registered=1');
                    exit;
                }
                $errors[] = 'Impossible de créer le compte pour le moment.';
            }

            $old = [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'field_id' => $fieldId
            ];
        }

        $this->render('auth/register', [
            'title' => 'Inscription',
            'errors' => $errors,
            'fields' => $fields,
            'old' => $old
        ]);
    }

    public function logout()
    {
        Auth::start();
        Auth::logout();

        header('Location: /login');
        exit;
    }
}
