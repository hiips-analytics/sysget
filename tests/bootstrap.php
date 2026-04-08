<?php
// Autoload personnalisé pour les tests (sans Composer)
spl_autoload_register(function ($class) {
    // Convertir le namespace en chemin de fichier
    $file = str_replace('\\', '/', $class) . '.php';

    // Chercher dans le répertoire app/
    $appFile = __DIR__ . '/../app/' . $file;
    if (file_exists($appFile)) {
        require_once $appFile;
        return true;
    }

    // Chercher dans le répertoire tests/
    $testFile = __DIR__ . '/' . $file;
    if (file_exists($testFile)) {
        require_once $testFile;
        return true;
    }

    return false;
});

// Inclure manuellement les fichiers principaux si l'autoload échoue
require_once __DIR__ . '/../app/Models/Model.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Session.php';
require_once __DIR__ . '/../app/Models/Course.php';
require_once __DIR__ . '/../app/Models/Classroom.php';
require_once __DIR__ . '/../app/Core/Database.php';

// Configuration de base de données pour les tests
define('DB_HOST', 'localhost');
define('DB_NAME', 'sysget_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Fonction helper pour créer une connexion de test
function createTestDatabase() {
    return new PDO('sqlite::memory:');
}