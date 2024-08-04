<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class LoginController extends Controller
{
    private string $template = 'login.twig';

    public function __construct(private readonly UserRepository $userRepository = new UserRepository())
    {

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

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function login(array $logindata): void
    {
        $email = $this->userRepository->findBy('email', $logindata['user']);
        $username = $this->userRepository->findBy('username', $logindata['user']);
        $this->userRepository->closeDB();
        $password = hash('sha256', $logindata['password']);
        if(!empty($email->current())){
            $user = $email->current();
        }
        elseif (!empty($username->current())){
            $user = $username->current();
        }
        else{
            $this->render($this->template, ['login_error' => true, 'user' => $logindata['user']]);
        }
        if(isset($user) && $user->getPassword() === $password){
            session_name('login');
            session_unset();
            session_start();
            $_SESSION['id'] = $user->getToken();
            header('Location: ' . $this->url . '/user');
        }
        else{
            $this->render($this->template, ['login_error' => true, 'user' => $logindata['user']]);
        }
    }
}