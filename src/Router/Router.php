<?php

namespace App\Router;
class Router{

    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function getRoute(): void
    {
        $file = __DIR__ . "/../Controller/" . ucfirst(substr($this->uri,1)) . "Controller.php";
        if(file_exists($file)){
            if($this->uri === '/'){
                require_once __DIR__ . "/../Controller/HomeController.php";
            }
            else{
                require_once $file;
            }
        }
        else{
            require_once __DIR__ . "/../Controller/404Controller.php";
        }
    }

}