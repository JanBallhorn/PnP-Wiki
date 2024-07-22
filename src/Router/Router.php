<?php

namespace App\Router;
class Router{

    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function getRoute(): string
    {
        $file = __DIR__ . "/../Views" . $this->uri . ".twig";
        if(file_exists($file)){
            return $this->uri . ".twig";
        }
        else if($this->uri === '/'){
            return 'home.twig';
        }
        else{
            return "404 Not Found";
        }
    }

}