<?php

namespace App\Model;

class ArticleSource
{
    private int $id;
    private Article $article;
    private Source $source;
    private string $page;
    private string $link;

    /**
     * @param int $id
     * @param Article $article
     * @param Source $source
     * @param string $page
     * @param string $link
     */
    public function __construct(int $id, Article $article, Source $source, string $page, string $link)
    {
        $this->id = $id;
        $this->article = $article;
        $this->source = $source;
        $this->page = $page;
        $this->link = $link;
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
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @param Source $source
     */
    public function setSource(Source $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getPage(): string
    {
        return $this->page;
    }

    /**
     * @param string $page
     */
    public function setPage(string $page): void
    {
        $this->page = $page;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

}