<?php

namespace App\Model;

class ArticleInfoContent
{
    private int $id;
    private string $topic;
    private string $content;
    private string $headline;
    private int $sequence;

    /**
     * @param int $id
     * @param string $topic
     * @param string $content
     * @param string $headline
     * @param int $sequence
     */
    public function __construct(int $id, string $topic, string $content, string $headline, int $sequence)
    {
        $this->id = $id;
        $this->topic = $topic;
        $this->content = $content;
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
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
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