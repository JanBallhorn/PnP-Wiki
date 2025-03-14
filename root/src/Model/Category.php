<?php

namespace App\Model;

use DateTime;

class Category
{
    private int $id;
    private string $name;
    private string $description;
    private DateTime $published;
    private User $createdBy;
    private DateTime $lastEdit;
    private User $lastEditBy;
    private ?string $icon;

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param DateTime $published
     * @param User $createdBy
     * @param DateTime $lastEdit
     * @param User $lastEditBy
     * @param string|null $icon
     */
    public function __construct(int $id, string $name, string $description, DateTime $published, User $createdBy, DateTime $lastEdit, User $lastEditBy, ?string $icon)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->published = $published;
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
        $this->icon = $icon;
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return DateTime
     */
    public function getPublished(): DateTime
    {
        return $this->published;
    }

    /**
     * @param DateTime $published
     */
    public function setPublished(DateTime $published): void
    {
        $this->published = $published;
    }

    /**
     * @return User
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     */
    public function setCreatedBy(User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return DateTime
     */
    public function getLastEdit(): DateTime
    {
        return $this->lastEdit;
    }

    /**
     * @param DateTime $lastEdit
     */
    public function setLastEdit(DateTime $lastEdit): void
    {
        $this->lastEdit = $lastEdit;
    }

    /**
     * @return User
     */
    public function getLastEditBy(): User
    {
        return $this->lastEditBy;
    }

    /**
     * @param User $lastEditBy
     */
    public function setLastEditBy(User $lastEditBy): void
    {
        $this->lastEditBy = $lastEditBy;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     */
    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }
}