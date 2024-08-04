<?php

namespace App\Model;

class ArticleCategory
{
    private int $id;
    private int $articleId;
    private int $categoryId;

    /**
     * @param int $id
     * @param int $articleId
     * @param int $categoryId
     */
    public function __construct(int $id, int $articleId, int $categoryId)
    {
        $this->id = $id;
        $this->articleId = $articleId;
        $this->categoryId = $categoryId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getArticleId(): int
    {
        return $this->articleId;
    }

    public function setArticleId(int $articleId): void
    {
        $this->articleId = $articleId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }
}