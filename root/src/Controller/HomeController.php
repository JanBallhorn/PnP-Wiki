<?php

namespace App\Controller;

class HomeController extends Controller
{
    private string $template = 'home.twig';
    public function __construct(){
        $this->render($this->template);
    }
}