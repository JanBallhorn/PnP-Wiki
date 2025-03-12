<?php

namespace App\Controller;


use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ImprintController extends Controller
{
    private string $template = 'imprint.twig';

    public function __construct(){
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index(): void
    {
        $this->render($this->template);
    }
}