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
    private ?ArticleInfoContentCollection $content;
    private ?ArticleInfoGalleryCollection $gallery;

    /**
     * @param int $id
     * @param Article $article
     * @param string $headline
     * @param ArticleInfoContentCollection|null $content
     * @param ArticleInfoGalleryCollection|null $gallery
     */
    public function __construct(int $id, Article $article, string $headline, ?ArticleInfoContentCollection $content, ?ArticleInfoGalleryCollection $gallery)
    {
        $this->id = $id;
        $this->article = $article;
        $this->headline = $headline;
        $this->content = $content;
        $this->gallery = $gallery;
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

    /**
     * @return ArticleInfoContentCollection|null
     */
    public function getContent(): ?ArticleInfoContentCollection
    {
        return $this->content;
    }

    /**
     * @param ArticleInfoContentCollection|null $content
     */
    public function setContent(?ArticleInfoContentCollection $content): void
    {
        $this->content = $content;
    }

    /**
     * @return ArticleInfoGalleryCollection|null
     */
    public function getGallery(): ?ArticleInfoGalleryCollection
    {
        return $this->gallery;
    }

    /**
     * @param ArticleInfoGalleryCollection|null $gallery
     */
    public function setGallery(?ArticleInfoGalleryCollection $gallery): void
    {
        $this->gallery = $gallery;
    }

    public function getContentHeadlines(): ?array
    {
        $contentHeadlines = array();
        $contents = $this->getContent();
        if($contents !== null){
            $contents->rewind();
            for($i = 1; $i <= $contents->count(); $i++){
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
}