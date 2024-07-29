<?php

namespace App\Controller;

use App\Model\User;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RegisterController extends Controller
{
    private string $template = 'register.twig';

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function register(array $userdata): void
    {
        $user = new User($userdata['firstname'], $userdata['lastname'], $userdata['email'], $userdata['username'], $userdata['password'], 0, 0);
        $user->setPassword(hash('sha256', $userdata['password']));
        $user->create();
        $this->render('thanks.twig', ['register' => true]);
    }
    public function getTemplate(): string
    {
        return $this->template;
    }
}