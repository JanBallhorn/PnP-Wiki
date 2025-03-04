<?php

namespace App\Model;

use App\Collection\ArticleInfoContentCollection;
use App\Collection\ArticleInfoGalleryCollection;
use App\Repository\ArticleInfoContentRepository;
use App\Repository\ArticleInfoGalleryRepository;

class ArticleInfo
{
    private int $id;
    private Article $article;
    private string $headline;

    /**
     * @param int $id
     * @param Article $article
     * @param string $headline
     */
    public function __construct(int $id, Article $article, string $headline)
    {
        $this->id = $id;
        $this->article = $article;
        $this->headline = $headline;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Article
     */
    public function getArticle(): Article
    {
        return $this->article;
    }

    /**
     * @param Article $article
     */
    public function setArticle(Article $article): void
    {
        $this->article = $article;
    }

    /**
     * @return string
     */
    public function getHeadline(): string
    {
        return $this->headline;
    }

    /**
     * @param string $headline
     */
    public function setHeadline(string $headline): void
    {
        $this->headline = $headline;
    }

    public function getContent(): ArticleInfoContentCollection
    {
        return (new ArticleInfoContentRepository())->findBy('info', $this->getId(), 'sequence');
    }

    public function getContentHeadlines(): ?array
    {
        $contentHeadlines = array();
        $contents = $this->getContent();
        if($contents->count() > 0){
            for($i = 1; $i < $contents->count(); $i++){
                $contentHeadlines[] = $contents->current()->getHeadline();
                $contents->next();
            }
            $contents->rewind();
            return array_unique($contentHeadlines, SORT_REGULAR);
        }
        else{
            return null;
        }
    }

    public function getGallery(): ArticleInfoGalleryCollection
    {
        return (new ArticleInfoGalleryRepository())->findBy('info', $this->getId(), 'sequence');
    }
}