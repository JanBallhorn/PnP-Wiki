<?php

namespace App\Model;

class ArticleCategory extends Model
{
    private int $id;
    private int $articleId;
    private int $categoryId;

    /**
     * @param int $articleId
     * @param int $categoryId
     */
    public function __construct(int $articleId, int $categoryId)
    {
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

    public function create(): void
    {
        $conn = $this->dbConnect();
        $stmt = $conn->prepare("INSERT INTO `article_categories` (`article`, `category`)
        VALUES (?, ?)");
        $conn->execute_query($stmt, [$this->articleId, $this->categoryId]);
        $this->closeConnection($conn);
    }
}