<?php

namespace App\Router;
use App\Controller\Error404Controller;
use App\Controller\HomeController;
use App\Repository\UserRepository;

class Router{

    private $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    public function execRoute(): void
    {
        if(stripos($this->uri, '?')){
            $uri = explode('/', substr($this->uri, 1, stripos($this->uri, '?') - 1));
        }
        else{
            $uri = explode('/', substr($this->uri, 1));
        }
        $file = __DIR__ . "/../Controller/" . ucfirst($uri[0]) . "Controller.php";
        if(file_exists($file)){
            if($this->uri === '/'){
                new HomeController();
            }
            else{
                $controller = "App\Controller\\" . ucfirst($uri[0]) . "Controller";
                $controller = new $controller();
                if(!empty($uri[1])){
                    $method = $uri[1];
                    if(!empty($_POST)){
                        $controller->$method($_POST);
                    }
                    elseif(count($_GET) > 1){
                        $controller->$method($_GET);
                    }
                    else{
                        $controller->$method();
                    }
                }
                else{
                    $controller->index();
                }
            }
        }
        else{
            new Error404Controller();
        }
    }

}