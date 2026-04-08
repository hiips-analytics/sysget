<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Auth;
use App\Core\Router;

Auth::start();

$app = new Router();
$app->run();

define('ROOT', dirname(__DIR__));
