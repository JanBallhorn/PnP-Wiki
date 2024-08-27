<?php

namespace App\Model;

class ListElement
{
    private int $id;
    private ArticleList $list;
    private Article $article;
    private string $name;

    /**
     * @param int $id
     * @param ArticleList $list
     * @param Article $article
     * @param string $name
     */
    public function __construct(int $id, ArticleList $list, Article $article, string $name)
    {
        $this->id = $id;
        $this->list = $list;
        $this->article = $article;
        $this->name = $name;
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
     * @return ArticleList
     */
    public function getList(): ArticleList
    {
        return $this->list;
    }

    /**
     * @param ArticleList $list
     */
    public function setList(ArticleList $list): void
    {
        $this->list = $list;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

}