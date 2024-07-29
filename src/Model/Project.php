<?php

namespace App\Model;

class Project extends Model
{
    private int $id;
    private string $name;
    private int $published;
    private int $createdBy;
    private int $lastEdit;
    private int $lastEditBy;
    private int $parentProject;
    private int $private;

    /**
     * @param string $name
     * @param int $createdBy
     * @param int $lastEdit
     * @param int $lastEditBy
     * @param int $parentProject
     * @param int $private
     */
    public function __construct(string $name, int $createdBy, int $lastEdit, int $lastEditBy, int $parentProject, int $private)
    {
        $this->name = $name;
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
        $this->parentProject = $parentProject;
        $this->private = $private;
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
     * @return int
     */
    public function getParentProject(): int
    {
        return $this->parentProject;
    }

    /**
     * @param int $parentProject
     */
    public function setParentProject(int $parentProject): void
    {
        $this->parentProject = $parentProject;
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
    public function create(): void
    {
        $conn = $this->dbConnect();
        $stmt = "INSERT INTO `projects` (`name`, `createdBy`, `lastEditBy`, `parentProject`, `private`) VALUES (?, ?, ?, ?, ?)";
        $conn->execute_query($stmt, [$this->name, $this->createdBy, $this->createdBy, $this->parentProject, $this->private]);
        $this->closeConnection($conn);
    }
}