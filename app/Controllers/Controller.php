<?php
namespace App\Controllers;

abstract class Controller 
{
    /**
     * @param string $view Nom du fichier (ex: 'admin/index')
     * @param array $data Données à transmettre à la vue
     */

    protected function render(string $view, array $data = []) 
    {
        // 
        extract($data);
        
        // On construit le chemin complet vers la vue
        $viewPath = __DIR__ . "/../../resources/views/{$view}.hiips.php";

        if (!file_exists($viewPath)) {
            die("La vue {$view} est introuvable dans " . $viewPath);
        }

        ob_start();
        require_once $viewPath;
        $content = ob_get_clean();

        // Idem pour le layout
        require_once __DIR__ . "/../../resources/views/layouts/app.hiips.php";
    }

    protected function clean($data) {
        return stripslashes(trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')));
    }
}