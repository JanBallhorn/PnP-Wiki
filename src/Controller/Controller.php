<?php

namespace App\Controller;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
    protected string $url = "https://wiki.verplant-durch-aventurien.de";
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    function render(string $view, array $params = []): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $twig = new Environment($loader);
        $twigParams = [];
        session_name('login');
        session_start();
        //var_dump($_SESSION);
        if(!empty($_SESSION)){
            $twigParams["loggedIn"] = true;
        }
        foreach ($params as $key => $value) {
            $twigParams[$key] = $value;
        }
        echo $twig->render($view, $twigParams);
    }
}