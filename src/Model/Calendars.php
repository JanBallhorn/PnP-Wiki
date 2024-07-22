<?php

namespace App\Model;

class Calendars
{
    private int $id;
    private int $published;
    private int $createdBy;
    private int $lastEdit;
    private int $lastEditBy;
    private string $name;
    private int $year0BF;
    private int $private;
    private int $editable;

    /**
     * @param int $createdBy
     * @param int $lastEdit
     * @param int $lastEditBy
     * @param string $name
     * @param int $year0BF
     * @param int $private
     * @param int $editable
     */
    public function __construct(int $createdBy, int $lastEdit, int $lastEditBy, string $name, int $year0BF, int $private, int $editable)
    {
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
        $this->name = $name;
        $this->year0BF = $year0BF;
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
    public function getYear0BF(): int
    {
        return $this->year0BF;
    }

    /**
     * @param int $year0BF
     */
    public function setYear0BF(int $year0BF): void
    {
        $this->year0BF = $year0BF;
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