<?php

namespace App\Model;

class Timeline
{
    private int $id;
    private int $published;
    private int $createdBy;
    private int $projectId;
    private int $private;
    private int $editable;

    /**
     * @param int $id
     * @param int $published
     * @param int $createdBy
     * @param int $projectId
     * @param int $private
     * @param int $editable
     */
    public function __construct(int $id, int $published, int $createdBy, int $projectId, int $private, int $editable)
    {
        $this->id = $id;
        $this->published = $published;
        $this->createdBy = $createdBy;
        $this->projectId = $projectId;
        $this->private = $private;
        $this->editable = $editable;
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
    public function getProjectId(): int
    {
        return $this->projectId;
    }

    /**
     * @param int $projectId
     */
    public function setProjectId(int $projectId): void
    {
        $this->projectId = $projectId;
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
}