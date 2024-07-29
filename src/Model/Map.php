<?php

namespace App\Model;

class Map extends Model
{
    private int $id;
    private int $published;
    private int $createdBy;
    private string $name;
    private int $project;
    private string $img;

    /**
     * @param int $createdBy
     * @param string $name
     * @param int $project
     * @param string $img
     */
    public function __construct(int $createdBy, string $name, int $project, string $img)
    {
        $this->createdBy = $createdBy;
        $this->name = $name;
        $this->project = $project;
        $this->img = $img;
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

    public function create(): void
    {
        $conn = $this->dbConnect();
        $stmt = "INSERT INTO `maps` (`createdBy`, `name`, `project`, `img`) VALUES (?, ?, ?, ?)";
        $conn->execute_query($stmt, [$this->createdBy, $this->name, $this->project, $this->img]);
        $this->closeConnection($conn);
    }
}