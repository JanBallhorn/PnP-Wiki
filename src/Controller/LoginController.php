<?php

namespace App\Controller;

use App\Model\User;

class LoginController extends Controller
{
    private string $template = 'login.twig';
    private string $table = 'users';
    public function getTemplate(): string
    {
        return $this->template;
    }
    public function login(array $logindata): void
    {
        $user = new User();
        $email = $user->findBy('email', $logindata['user'], $this->table);
        $username = $user->findBy('username', $logindata['user'], $this->table);
        $password = hash('sha256', $logindata['password']);
        if(!empty($email)){
            $user = $email[0];
        }
        elseif (!empty($username)){
            $user = $username[0];
        }
        else{
            $this->render($this->template, ['login_error' => true, 'user' => $logindata['user']]);
        }
        if($user['password'] === $password){
            session_name('login');
            if($logindata['remember'] === 'on'){
                session_start(['cookie_lifetime' => 15768000]);
            }
            else{
                session_start();
            }
            $_SESSION['id'] = $user['token'];
            header('Location: ' . $this->url . '/user');
        }
        else{
            $this->render($this->template, ['login_error' => true, 'user' => $logindata['user']]);
        }
    }
}