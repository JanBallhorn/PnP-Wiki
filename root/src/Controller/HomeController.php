<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController extends Controller
{
    private string $template = 'home.twig';

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function __construct(){
        if($this->getCookie() !== null){
            $username = $this->getUsernameFromToken($this->getCookie());
            $user = (new UserRepository())->findOneBy('username', $username);
            $projects = (new ProjectRepository())->findAllBetween(0, 5, $user->getId(), 'searched DESC');
            $categories = (new CategoryRepository())->findPopularCategories();
            $popularArticles = (new ArticleRepository())->findAllBetween(0, 5, $user->getId(), 'called DESC');
            $newArticles = (new ArticleRepository())->findAllBetween(0, 5, $user->getId(), 'published DESC');
            $this->render($this->template, [
                'projects' => $projects,
                'categories' => $categories,
                'popularArticles' => $popularArticles,
                'newArticles' => $newArticles
            ]);
        }
        else{
            $this->render($this->template);
        }
    }
}