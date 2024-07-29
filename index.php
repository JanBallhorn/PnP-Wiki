<?php

use App\Router\Router;

require_once __DIR__ . '/inc/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/Controller/RegisterController.php';

$uri = $_SERVER['REQUEST_URI'];
$router = new Router($uri);
$router->execRoute();