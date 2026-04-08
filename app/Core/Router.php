<?php
namespace App\Core;

class Router 
{
    public function run() 
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');
        $parts = !empty($uri) ? explode('/', $uri) : ['home'];

        $page = $parts[0];
        $action = $parts[1] ?? 'index';
        $params = [];

        if (in_array($page, ['login', 'register', 'logout'], true)) {
            $controllerName = "App\\Controllers\\AuthController";
            $action = $page;
        } elseif ($page === 'auth') {
            $controllerName = "App\\Controllers\\AuthController";
            $action = $parts[1] ?? 'login';
            $params = array_slice($parts, 2);
        } else {
            $controllerName = "App\\Controllers\\" . ucfirst($page) . "Controller";
            $params = array_slice($parts, 2);
        }

        if (class_exists($controllerName)) {
            $controller = new $controllerName;
            if (method_exists($controller, $action)) {
                call_user_func_array([$controller, $action], $params);
            } else {
                http_response_code(404);
                echo "Action '$action' non trouvée!";
            }
        } else {
            http_response_code(404);
            echo "Page '$page' non trouvée (Classe $controllerName absente)!";
        }
    }
}
