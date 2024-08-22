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
    public function login(array $loginData): void
    {
        $email = $this->userRepository->findOneBy('email', $loginData['user']);
        $username = $this->userRepository->findOneBy('username', $loginData['user']);
        $password = hash('sha256', $loginData['password']);
        if(!empty($email)){
            $user = $email;
        }
        elseif (!empty($username)){
            $user = $username;
        }
        else{
            $this->render($this->template, ['loginError' => true, 'user' => $loginData['user']]);
        }
        if(isset($user) && $user->getPassword() === $password){
            if($user->getVerified()){
                if(isset($loginData['remember'])){
                    $remember = true;
                }
                else{
                    $remember = false;
                }
                $userId = $user->getId();
                $username = $user->getUsername();
                $token = $this->buildToken($username, $userId, $remember);
                $this->createCookie($token, $remember);
                header('Location: ' . $this->url . '/profile?' . http_build_query(['user'=>$username]));
            }
            else{
                $this->render($this->template, ['verificationError' => true, 'user' => $loginData['user']]);
            }
        }
        else{
            $this->render($this->template, ['loginError' => true, 'user' => $loginData['user']]);
        }
    }
}