<?php

namespace App\Controller;

use App\LoginThrottle;
use App\Model\User;
use App\Repository\UserRepository;
use Exception;
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
     * @throws Exception
     */
    public function login(array $loginData): void
    {
        $identifier = trim($loginData['user'] ?? '');
        $throttle = new LoginThrottle();
        if($identifier === '' || $throttle->isBlocked($identifier)){
            $this->render($this->template, ['loginError' => true, 'rateLimited' => true, 'user' => $loginData['user'] ?? '']);
            return;
        }
        $email = $this->userRepository->findOneBy('email', $loginData['user']);
        $username = $this->userRepository->findOneBy('username', $loginData['user']);
        if(!empty($email)){
            $user = $email;
        }
        elseif (!empty($username)){
            $user = $username;
        }
        else{
            $throttle->recordFailure($identifier);
            $this->render($this->template, ['loginError' => true, 'user' => $loginData['user']]);
            return;
        }
        if($this->verifyPassword($loginData['password'], $user)){
            if($user->getVerified()){
                $throttle->clear($identifier);
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
                header('Location: ' . $this->url . '/profile?' . http_build_query(['id'=>$userId]));
            }
            else{
                $this->render($this->template, ['verificationError' => true, 'user' => $loginData['user']]);
            }
        }
        else{
            $throttle->recordFailure($identifier);
            $this->render($this->template, ['loginError' => true, 'user' => $loginData['user']]);
        }
    }

    /**
     * Verifies the password against the stored hash. Accounts registered
     * before the switch to password_hash() still have a plain sha256 hash
     * stored; those are verified against the legacy scheme once and then
     * transparently upgraded so every login after this one uses
     * password_hash()/password_verify().
     */
    private function verifyPassword(string $plainPassword, User $user): bool
    {
        if(password_verify($plainPassword, $user->getPassword())){
            return true;
        }
        if(hash_equals($user->getPassword(), hash('sha256', $plainPassword))){
            $user->setPassword(password_hash($plainPassword, PASSWORD_DEFAULT));
            $this->userRepository->save($user);
            return true;
        }
        return false;
    }
}