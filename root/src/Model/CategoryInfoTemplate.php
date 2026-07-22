<?php

namespace App\Model;

/**
 * One field of a category's infobox template - a group headline plus a field
 * label (topic), without a value. When an article is created with the template,
 * these become empty ArticleInfoContent rows the author fills in.
 */
class CategoryInfoTemplate
{
    private int $id;
    private int $category;
    private string $headline;
    private string $topic;
    private int $sequence;

    /**
     * @param int $id
     * @param int $category
     * @param string $headline
     * @param string $topic
     * @param int $sequence
     */
    public function __construct(int $id, int $category, string $headline, string $topic, int $sequence)
    {
        $this->id = $id;
        $this->category = $category;
        $this->headline = $headline;
        $this->topic = $topic;
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
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     */
    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
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
