<?php

namespace App\Model;

use App\Repository\ArticleRepository;
use App\Repository\SourceRepository;

class ArticleSource
{
    private int $id;
    private Article $article;
    private Source $source;
    private string $reference;

    /**
     * @param int $id
     * @param Article $article
     * @param Source $source
     * @param string $reference
     */
    public function __construct(int $id, Article $article, Source $source, string $reference)
    {
        $this->id = $id;
        $this->article = $article;
        $this->source = $source;
        $this->reference = $reference;
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
     * @return Article
     */
    public function getArticle(): Article
    {
        return $this->article;
    }

    /**
     * @param Article $article
     */
    public function setArticle(Article $article): void
    {
        $this->article = $article;
    }

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @param Source $source
     */
    public function setSource(Source $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function getType(): string
    {
        return $this->source->getType();
    }

    public function getName(): string
    {
        return $this->source->getName();
    }

}