<?php

namespace App\Controller;

use App\Collection\CategoryCollection;
use App\Model\Article;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use DateTime;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ArticleController extends Controller
{
    public function __construct(private readonly ArticleRepository $articleRepository = new ArticleRepository(), private readonly CategoryRepository $categoryRepository = new CategoryRepository(), private readonly ProjectRepository $projectRepository = new ProjectRepository(), private readonly UserRepository $userRepository = new UserRepository()){

    }

    /**
     * @throws Exception
     */
    public function index(array $article): void
    {
        $article = $this->articleRepository->findOneBy('headline', $article['name']);
        $this->render('article.twig', ['article' => $article]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function create(): void
    {
        $projects = $this->projectRepository->findAll('name');
        $categories = $this->categoryRepository->findAll('name');
        $this->render('createArticle.twig', ['projects' => $projects->__serialize(), 'categories' => $categories->__serialize()]);
    }

    /**
     * @throws Exception
     */
    public function save(array $article): void
    {
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $sameHeadline = $this->articleRepository->findOneBy('headline', $article['headline']);
        if($sameHeadline === null && !empty($article['project']) && isset($article['category'])){
            $categories = new CategoryCollection();
            foreach ($article['category'] as $category){
                $category = $this->categoryRepository->findById(intval($category));
                $categories->offsetSet($categories->key(), $category);
                $categories->next();
            }
            $categories->rewind();
            $project = $this->projectRepository->findOneBy('name', $article['project']);
            $tags = explode(",", $article['tags']);
            $altHeadlines = array_filter($article['altHeadlines'], function($h){return(!empty($h));});
            $article = new Article(0, new DateTime(), $user, new DateTime(), $user, $article['headline'], $project, $categories, $tags, $altHeadlines, isset($article['private']), isset($article['editable']), 0);
            $this->articleRepository->save($article);
            header("Location: /article?" . http_build_query(['name' => $article->getHeadline()]));
        }
        else{
            $projects = $this->projectRepository->findAll('name');
            $categories = $this->categoryRepository->findAll('name');
            $this->render('createArticle.twig', [
                'articleError' => true,
                'projects' => $projects->__serialize(),
                'categories' => $categories->__serialize(),
                'headline' => $article['headline'],
                'project' => $article['project'],
                'categoryIds' => $article['category'],
                'categoryError' => !isset($article['category']),
                'altHeadline1' => $article['altHeadline'][0],
                'altHeadline2' => $article['altHeadline'][1],
                'altHeadline3' => $article['altHeadline'][2],
                'altHeadline4' => $article['altHeadline'][3],
                'tags' => $article['tags'],
                'private' => isset($article['private']),
                'editable' => isset($article['editable'])
            ]);
        }
    }
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function list(array $filter): void
    {
        $page = $filter['page'];
        $filter = $filter['filter'];
        if($filter === 'headline_down'){
            $filter = 'headline DESC';
        }
        elseif($filter === 'published_new'){
            $filter = 'published DESC';
        }
        $articles = $this->articleRepository->findAllBetween(($page - 1) * 50 + 1, ($page - 1) * 50 + 50, $filter);
        $this->render('articleList.twig', ['articles' => $articles, 'filter' => $filter]);
    }
}