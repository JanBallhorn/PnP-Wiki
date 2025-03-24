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

class SearchController extends Controller
{
    public function __construct(
        private readonly ArticleRepository $articleRepository = new ArticleRepository(),
        private readonly CategoryRepository $categoryRepository = new CategoryRepository(),
        private readonly ProjectRepository $projectRepository = new ProjectRepository(),
        private readonly UserRepository $userRepository = new UserRepository()
    ){}

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index(): void
    {
        $this->render('search.twig');
    }

    /**
     * @throws Exception
     */
    public function search(array $query): void
    {
        if(isset($query['category'])){
            $category = $this->categoryRepository->findById($query['category']);
            $articles = $this->articleRepository->search($query['search'], $category);
        }
        elseif (isset($query['project'])){
            $project = $this->projectRepository->findById($query['project']);
            $articles = $this->articleRepository->search($query['search'], null, $project);
        }
        else{
            $articles = $this->articleRepository->search($query['search']);
        }
        $ids = [];
        $offsets = [];
        $articles->rewind();
        if($articles->current() !== null){
            $username = $this->getUsernameFromToken($this->getCookie());
            $user = $this->userRepository->findOneBy('username', $username);
            for($i = 0; $i < $articles->count(); $i++){
                $id = $articles->current()->getId();
                if($articles->current()->getPrivate()){
                    $authorizedIds = array();
                    $authorized = $articles->current()->getAuthorized();
                    $authorized->rewind();
                    for($i = 0; $i < $authorized->count(); $i++){
                        $authorizedIds[] = $authorized->current()->getId();
                        $authorized->next();
                    }
                    if(!in_array($user->getId(), $authorizedIds) || in_array($id, $ids)){
                        $offsets[] = $articles->key();
                    }
                    else{
                        $ids[] = $id;
                    }
                }
                elseif(!in_array($id, $ids)){
                    $ids[] = $id;
                }
                else{
                    $offsets[] = $articles->key();
                }
                $articles->next();
            }
            foreach($offsets as $offset){
                $articles->offsetUnset($offset);
            }
        }
        if(isset($query['category'])){
            $category = $this->categoryRepository->findById($query['category']);
            $this->render('categoryDetail.twig', [
                'category' => $category,
                'searched' => true,
                'articles' => $articles,
                'query' => $query['search']
            ]);
        }
        elseif (isset($query['project'])){
            $project = $this->projectRepository->findById($query['project']);
            $project->setSearched($project->getSearched() + 1);
            $this->projectRepository->save($project);
            $this->render("projectDetail.twig", [
                'project' => $project,
                'searched' => true,
                'articles' => $articles,
                'query' => $query['search']
            ]);
        }
        else{
            $this->render('search.twig', [
                'searched' => true,
                'articles' => $articles,
                'query' => $query['search']
            ]);
        }
    }
}