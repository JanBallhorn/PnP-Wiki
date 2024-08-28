<?php

namespace App\Model;

class ArticleInfo
{
    private int $id;
    private Article $article;
    private string $headline;
    private string $img;
    private string $figcaption;

    /**
     * @param int $id
     * @param Article $article
     * @param string $headline
     * @param string $img
     * @param string $figcaption
     */
    public function __construct(int $id, Article $article, string $headline, string $img, string $figcaption)
    {
        $this->id = $id;
        $this->article = $article;
        $this->headline = $headline;
        $this->img = $img;
        $this->figcaption = $figcaption;
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
     * @return string
     */
    public function getImg(): string
    {
        return $this->img;
    }

    /**
     * @param string $img
     */
    public function setImg(string $img): void
    {
        $this->img = $img;
    }

    /**
     * @return string
     */
    public function getFigcaption(): string
    {
        return $this->figcaption;
    }

    /**
     * @param string $figcaption
     */
    public function setFigcaption(string $figcaption): void
    {
        $this->figcaption = $figcaption;
    }

}