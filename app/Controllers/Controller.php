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
        
        $file = __DIR__ . "/../../resources/views/" . $view . ".hiips.php";

        if (file_exists($file)) {
            require_once $file;
        } else {
            die("La vue '$view' n'existe pas!");
        }
    }

    protected function clean($data) {
        return stripslashes(trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')));
    }
}