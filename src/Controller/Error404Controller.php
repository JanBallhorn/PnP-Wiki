<?php

namespace App\Controller;

class Error404Controller extends Controller
{
    private string $template = 'error404.twig';
    public function __construct(){
        $this->render($this->template);
    }
}