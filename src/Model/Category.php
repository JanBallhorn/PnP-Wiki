<?php

namespace App\Model;

class Category extends Model
{
    private int $id;
    private string $name;
    private int $published;
    private int $createdBy;
    private int $lastEdit;
    private int $lastEditBy;
    private string $icon;

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
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function create(): void
    {
        $conn = $this->dbConnect();
        $stmt = "INSERT INTO `categories` (`name`, `created_by`, `last_edit_by`, `icon`) VALUES (?, ?, ?, ?)";
        $conn->execute_query($stmt, [$this->name, $this->createdBy, $this->createdBy, $this->icon]);
        $this->closeConnection($conn);
    }
}