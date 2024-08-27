<?php

namespace App\Model;

use App\Collection\ListElementCollection;
use App\Repository\ListElementRepository;
use DateTime;
use Exception;

class ArticleList
{
    private int $id;
    private Category $category;
    private DateTime $published;
    private User $createdBy;
    private DateTime $lastEdit;
    private User $lastEditBy;
    private string $name;

    /**
     * @param int $id
     * @param Category $category
     * @param DateTime $published
     * @param User $createdBy
     * @param DateTime $lastEdit
     * @param User $lastEditBy
     * @param string $name
     */
    public function __construct(int $id, Category $category, DateTime $published, User $createdBy, DateTime $lastEdit, User $lastEditBy, string $name)
    {
        $this->id = $id;
        $this->category = $category;
        $this->published = $published;
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
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
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
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
     * @return ListElementCollection
     * @throws Exception
     */
    public function getElements(): ListElementCollection
    {
        return (new ListElementRepository())->findBy('list', $this->getId(), 'element_name');
    }


}