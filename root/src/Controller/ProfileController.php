<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ProfileController extends Controller
{
    private string $template = 'profile.twig';
    public function __construct(private readonly UserRepository $userRepository = new UserRepository(), private readonly ArticleRepository $articleRepository = new ArticleRepository())
    {

    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    public function index(array $username): void
    {
        $username = $username['user'];
        $user = $this->userRepository->findOneBy('username', $username);
        $profileTextExists = !empty($user->getProfileText());
        $articles = $this->articleRepository->findBy('created_by', $user->getId(), "published DESC");
        if($articles !== null){
            $articles->rewind();
            $createdArticles = $articles->count();
            $newestArticle = $articles->current();
        }
        else{
            $createdArticles = 0;
            $newestArticle = null;
        }
        $templateData = [
            'ownProfile'=> $this->checkOwnProfile($user->getUsername()),
            'username'=>$user->getUsername(),
            'firstnamePublic'=>$user->getFirstnamePublic(),
            'firstname'=>$user->getFirstname(),
            'lastnamePublic'=>$user->getLastnamePublic(),
            'lastname'=>$user->getLastname(),
            'profileTextExists'=>$profileTextExists,
            'profileText'=>$user->getProfileText(),
            'user_query'=>http_build_query(['user'=>$user->getUsername()]),
            'createdArticles'=>$createdArticles,
            'newestArticle'=>$newestArticle
        ];
        $this->render($this->template, $templateData);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    public function edit(array $username): void{
        $username = $username['user'];
        $user = $this->userRepository->findOneBy('username', $username);
        $articles = $this->articleRepository->findBy('created_by', $user->getId(), "published DESC");
        if($articles !== null){
            $articles->rewind();
            $createdArticles = $articles->count();
            $newestArticle = $articles->current();
        }
        else{
            $createdArticles = 0;
            $newestArticle = null;
        }
        $templateData = [
            'ownProfile'=> $this->checkOwnProfile($user->getUsername()),
            'editMode'=>true,
            'username'=>$user->getUsername(),
            'firstnamePublic'=>$user->getFirstnamePublic(),
            'lastnamePublic'=>$user->getLastnamePublic(),
            'profileText'=>$user->getProfileText(),
            'createdArticles'=>$createdArticles,
            'newestArticle'=>$newestArticle
        ];
        $this->render($this->template, $templateData);
    }

    /**
     * @throws Exception
     */
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