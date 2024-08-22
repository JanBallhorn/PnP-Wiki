<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ProfileController extends Controller
{
    private string $template = 'profile.twig';
    public function __construct(private readonly UserRepository $userRepository = new UserRepository())
    {

    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(array $username): void
    {
        $username = $username['user'];
        $user = $this->userRepository->findOneBy('username', $username);
        $profileTextExists = !empty($user->getProfileText());
        $templateData = [
            'ownProfile'=> $this->checkOwnProfile($user->getUsername()),
            'username'=>$user->getUsername(),
            'firstnamePublic'=>$user->getFirstnamePublic(),
            'firstname'=>$user->getFirstname(),
            'lastnamePublic'=>$user->getLastnamePublic(),
            'lastname'=>$user->getLastname(),
            'profileTextExists'=>$profileTextExists,
            'profileText'=>$user->getProfileText(),
            'user_query'=>http_build_query(['user'=>$user->getUsername()])
        ];
        $this->render($this->template, $templateData);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function edit(array $username): void{
        $username = $username['user'];
        $user = $this->userRepository->findOneBy('username', $username);
        $templateData = [
            'ownProfile'=> $this->checkOwnProfile($user->getUsername()),
            'editMode'=>true,
            'username'=>$user->getUsername(),
            'firstnamePublic'=>$user->getFirstnamePublic(),
            'lastnamePublic'=>$user->getLastnamePublic()
        ];
        $this->render($this->template, $templateData);
    }
    public function save(array $profileData): void{
        $user = $this->userRepository->findOneBy('username', $profileData['username']);
        $user->setFirstnamePublic(isset($profileData['firstnamePublic']));
        $user->setLastnamePublic(isset($profileData['lastnamePublic']));
        $user->setProfileText($profileData['profileText']);
        $this->userRepository->save($user);
        header("Location: /profile?" . http_build_query(['user'=>$user->getUsername()]));
    }
    protected function checkOwnProfile($username): bool{
        if($this->getCookie()){
            return $this->validateToken($this->getCookie(), $username);
        }
        else{
            return false;
        }
    }
}