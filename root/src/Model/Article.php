<?php

namespace App\Model;

use App\Collection\ArticleListCollection;
use App\Collection\CategoryCollection;
use App\Repository\ArticleInfoRepository;
use App\Repository\ArticleListRepository;
use App\Repository\ListElementRepository;
use DateTime;
use Exception;

class Article
{
    private int $id;
    private DateTime $published;
    private User $createdBy;
    private DateTime $lastEdit;
    private User $lastEditBy;
    private string $headline;
    private Project $project;
    private ?CategoryCollection $categories;
    private bool $private;
    private bool $editable;
    private int $called;

    /**
     * @param int $id
     * @param DateTime $published
     * @param User $createdBy
     * @param DateTime $lastEdit
     * @param User $lastEditBy
     * @param string $headline
     * @param Project $project
     * @param CategoryCollection|null $categories
     * @param bool $private
     * @param bool $editable
     * @param int $called
     */
    public function __construct(int $id, DateTime $published, User $createdBy, DateTime $lastEdit, User $lastEditBy, string $headline, Project $project, ?CategoryCollection $categories, bool $private, bool $editable, int $called)
    {
        $this->id = $id;
        $this->published = $published;
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
        $this->headline = $headline;
        $this->project = $project;
        $this->categories = $categories;
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
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    /**
     * @return CategoryCollection|null
     */
    public function getCategories(): ?CategoryCollection
    {
        return $this->categories;
    }

    /**
     * @param CategoryCollection|null $categories
     */
    public function setCategories(?CategoryCollection $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return bool
     */
    public function getPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     */
    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    /**
     * @return bool
     */
    public function getEditable(): bool
    {
        return $this->editable;
    }

    /**
     * @param bool $editable
     */
    public function setEditable(bool $editable): void
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

    /**
     * @throws Exception
     */
    public function getInfo(): ArticleInfo
    {
        return (new ArticleInfoRepository())->findOneBy('article', $this->getId());
    }

    /**
     * @throws Exception
     */
    public function getLists(): ArticleListCollection
    {
        $listElements = (new ListElementRepository())->findBy('article', $this->getId());
        $lists = new ArticleListCollection();
        for($i = 1; $i <= $listElements->count(); $i++){
            $list = $listElements->current()->getList();
            $lists->offsetSet($lists->key(), $list);
            $lists->next();
            $listElements->next();
        }
        return $lists;
    }

    public function getContent(){

    }

}