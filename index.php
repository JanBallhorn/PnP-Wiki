<?php

use App\Router\Router;

require_once __DIR__ . '/inc/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

$router = new Router($_SERVER['REQUEST_URI']);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/src/Views');
$twig = new \Twig\Environment($loader);

echo $twig->render($router->getRoute(), []);