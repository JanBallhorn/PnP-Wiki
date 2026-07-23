<?php

namespace App\Model;

/**
 * One section of a category's article template - just a section headline plus
 * an order. When an article is created with the template, each becomes an empty
 * paragraph (headline only) the author fills in.
 */
class CategorySectionTemplate
{
    private int $id;
    private int $category;
    private string $headline;
    private int $sequence;

    /**
     * @param int $id
     * @param int $category
     * @param string $headline
     * @param int $sequence
     */
    public function __construct(int $id, int $category, string $headline, int $sequence)
    {
        $this->id = $id;
        $this->category = $category;
        $this->headline = $headline;
        $this->sequence = $sequence;
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
    public function getCategory(): int
    {
        return $this->category;
    }

    /**
     * @param int $category
     */
    public function setCategory(int $category): void
    {
        $this->category = $category;
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
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * @param int $sequence
     */
    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }
}
