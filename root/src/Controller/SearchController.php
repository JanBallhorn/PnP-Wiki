<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SearchController extends Controller
{
    public function __construct(
        private readonly ArticleRepository $articleRepository = new ArticleRepository()
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
        $articles = $this->articleRepository->search($query['search']);
        $ids = [];
        $offsets = [];
        if($articles->current() !== null){
            for($i = 1; $i <= $articles->count(); $i++){
                $id = $articles->current()->getId();
                if(!in_array($id, $ids)){
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
        $this->render('search.twig', [
            'searched' => true,
            'articles' => $articles,
            'query' => $query['search']
        ]);
    }
}