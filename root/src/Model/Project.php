<?php

namespace App\Model;

use App\Collection\ProjectCollection;
use App\Collection\UserCollection;
use App\Repository\ProjectRepository;
use DateTime;
use Exception;

class Project
{
    private int $id;
    private string $name;
    private string $description;
    private DateTime $published;
    private User $createdBy;
    private DateTime $lastEdit;
    private User $lastEditBy;
    private null|Project $parentProject;
    private bool $private;
    private ?UserCollection $authorized;
    private int $searched;

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param DateTime $published
     * @param User $createdBy
     * @param DateTime $lastEdit
     * @param User $lastEditBy
     * @param null|Project $parentProject
     * @param bool $private
     * @param UserCollection|null $authorized
     * @param int $searched
     */
    public function __construct(int $id, string $name, string $description, DateTime $published, User $createdBy, DateTime $lastEdit, User $lastEditBy, ?Project $parentProject, bool $private, ?UserCollection $authorized, int $searched)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->published = $published;
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
        $this->parentProject = $parentProject;
        $this->private = $private;
        $this->authorized = $authorized;
        $this->searched = $searched;
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
     * @return null|Project
     */
    public function getParentProject(): null|Project
    {
        return $this->parentProject;
    }

    /**
     * @param null|Project $parentProject
     */
    public function setParentProject(null|Project $parentProject): void
    {
        $this->parentProject = $parentProject;
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
     * @return UserCollection|null
     */
    public function getAuthorized(): ?UserCollection
    {
        return $this->authorized;
    }

    /**
     * @param UserCollection|null $authorized
     */
    public function setAuthorized(?UserCollection $authorized): void
    {
        $this->authorized = $authorized;
    }

    /**
     * @return int
     */
    public function getSearched(): int
    {
        return $this->searched;
    }

    /**
     * @param int $searched
     */
    public function setSearched(int $searched): void
    {
        $this->searched = $searched;
    }

    /**
     * @return ProjectCollection|null
     * @throws Exception
     */
    public function getChildren(): ?ProjectCollection
    {
        $rep = new ProjectRepository();
        $children = $rep->findBy('parent_project', $this->getId(), 'name');
        if($children !== null){
            return $children;
        }
        else{
            return null;
        }
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        $project = $this;
        $i = 1;
        while($project->getParentProject() !== null){
            $project = $project->getParentProject();
            $i++;
        }
        return $i;
    }
}