<?php

namespace App\Model;

class Article extends Model
{
    private int $id;
    private int $published;
    private int $createdBy;
    private int $lastEdit;
    private int $lastEditBy;
    private string $headline;
    private int $project;
    private string $img;
    private string $figcaption;
    private int $private;
    private int $editable;
    private int $called;

    /**
     * @param int $createdBy
     * @param int $lastEdit
     * @param int $lastEditBy
     * @param string $headline
     * @param int $project
     * @param string $img
     * @param string $figcaption
     * @param int $private
     * @param int $editable
     * @param int $called
     */
    public function __construct(int $createdBy, int $lastEdit, int $lastEditBy, string $headline, int $project, string $img, string $figcaption, int $private, int $editable, int $called)
    {
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
        $this->headline = $headline;
        $this->project = $project;
        $this->img = $img;
        $this->figcaption = $figcaption;
        $this->private = $private;
        $this->editable = $editable;
        $this->called = $called;
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
    public function getProject(): int
    {
        return $this->project;
    }

    /**
     * @param int $project
     */
    public function setProject(int $project): void
    {
        $this->project = $project;
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

    /**
     * @return int
     */
    public function getPrivate(): int
    {
        return $this->private;
    }

    /**
     * @param int $private
     */
    public function setPrivate(int $private): void
    {
        $this->private = $private;
    }

    /**
     * @return int
     */
    public function getEditable(): int
    {
        return $this->editable;
    }

    /**
     * @param int $editable
     */
    public function setEditable(int $editable): void
    {
        $this->editable = $editable;
    }

    /**
     * @return int
     */
    public function getCalled(): int
    {
        return $this->called;
    }

    /**
     * @param int $called
     */
    public function setCalled(int $called): void
    {
        $this->called = $called;
    }

    public function create(): void
    {
        $conn = $this->dbConnect();
        $stmt = "INSERT INTO `articles` (`created_by`, `last_edit_by`, `headline`, `project`, `img`, `figcaption`, `private`, `editable`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $conn->execute_query($stmt, [$this->createdBy, $this->createdBy, $this->headline, $this->project, $this->img, $this->figcaption, $this->private, $this->editable]);
        $this->closeConnection($conn);
    }
}