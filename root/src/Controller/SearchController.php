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
            $category = $this->categoryRepository->findOneBy('id', $query['category']);
            $articles = $this->articleRepository->search($query['search'], $category);
        }
        elseif (isset($query['project'])){
            $project = $this->projectRepository->findOneBy('id', $query['project']);
            $articles = $this->articleRepository->search($query['search'], null, $project);
        }
        else{
            $articles = $this->articleRepository->search($query['search']);
        }
        $ids = [];
        $offsets = [];
        if($articles->current() !== null){
            $username = $this->getUsernameFromToken($this->getCookie());
            $user = $this->userRepository->findOneBy('username', $username);
            for($i = 1; $i <= $articles->count(); $i++){
                $id = $articles->current()->getId();
                if($articles->current()->getPrivate() && $user->getId() !== $articles->current()->getCreatedBy()->getId()){
                    $offsets[] = $articles->key();
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
            $category = $this->categoryRepository->findOneBy('id', $query['category']);
            $this->render('categoryDetail.twig', [
                'category' => $category,
                'searched' => true,
                'articles' => $articles,
                'query' => $query['search']
            ]);
        }
        elseif (isset($query['project'])){
            $project = $this->projectRepository->findOneBy('id', $query['project']);
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