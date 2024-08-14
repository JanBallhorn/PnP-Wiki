<?php

namespace App;
use App\Controller\Error404Controller;
use App\Controller\HomeController;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;

class Router{

    private string $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @throws ReflectionException
     */
    public function execRoute(): void
    {
        if(stripos($this->uri, '?')){
            $uri = explode('/', substr($this->uri, 1, stripos($this->uri, '?') - 1));
        }
        else{
            $uri = explode('/', substr($this->uri, 1));
        }
        $file = __DIR__ . "/Controller/" . ucfirst($uri[0]) . "Controller.php";
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
                        if(!$this->needParams($controller, $method)){
                            $controller->$method();
                        }
                        else{
                            new Error404Controller();
                        }
                    }
                }
                else{
                    if(!empty($_POST)){
                        $controller->index($_POST);
                    }
                    elseif(count($_GET) > 1){
                        $controller->index($_GET);
                    }
                    else{
                        if(!$this->needParams($controller, 'index')){
                            $controller->index();
                        }
                        else{
                            new Error404Controller();
                        }
                    }
                }

            }
        }
        else{
            new Error404Controller();
        }
    }

    /**
     * @throws ReflectionException
     */
    private function needParams(object $controller, string $method): bool{
        $controller = new ReflectionClass($controller);
        $method = $controller->getMethod($method);
        return $method->getNumberOfParameters() > 0;
    }
}