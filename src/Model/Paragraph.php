<?php

namespace App\Model;

class Paragraph extends Model
{
    private int $id;
    private int $published;
    private int $createdBy;
    private int $lastEdit;
    private int $lastEditBy;
    private int $article;
    private string $headline;
    private int $order;

    /**
     * @param int $createdBy
     * @param int $lastEdit
     * @param int $lastEditBy
     * @param int $article
     * @param string $headline
     * @param int $order
     */
    public function __construct(int $createdBy, int $lastEdit, int $lastEditBy, int $article, string $headline, int $order)
    {
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
        $this->article = $article;
        $this->headline = $headline;
        $this->order = $order;
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
     * @return int
     */
    public function getPublished(): int
    {
        return $this->published;
    }

    /**
     * @param int $published
     */
    public function setPublished(int $published): void
    {
        $this->published = $published;
    }

    /**
     * @return int
     */
    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    /**
     * @param int $createdBy
     */
    public function setCreatedBy(int $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return int
     */
    public function getLastEdit(): int
    {
        return $this->lastEdit;
    }

    /**
     * @param int $lastEdit
     */
    public function setLastEdit(int $lastEdit): void
    {
        $this->lastEdit = $lastEdit;
    }

    /**
     * @return int
     */
    public function getLastEditBy(): int
    {
        return $this->lastEditBy;
    }

    /**
     * @param int $lastEditBy
     */
    public function setLastEditBy(int $lastEditBy): void
    {
        $this->lastEditBy = $lastEditBy;
    }

    /**
     * @return int
     */
    public function getArticle(): int
    {
        return $this->article;
    }

    /**
     * @param int $article
     */
    public function setArticle(int $article): void
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
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }
    public function create(): void
    {
        $conn = $this->dbConnect();
        $stmt = "INSERT INTO `paragraphs` (`created_by`, `last_edit_by`, `article`, `headline`, `order`) VALUES (?, ?, ?, ?, ?)";
        $conn->execute_query($stmt, [$this->createdBy, $this->createdBy, $this->article, $this->headline, $this->order]);
        $this->closeConnection($conn);
    }
}