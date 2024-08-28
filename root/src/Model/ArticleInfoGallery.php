<?php

namespace App\Model;

class ArticleInfoGallery
{
    private int $id;
    private ArticleInfo $info;
    private string $img;
    private string $figcaption;
    private int $sequence;

    /**
     * @param int $id
     * @param ArticleInfo $info
     * @param string $img
     * @param string $figcaption
     * @param int $sequence
     */
    public function __construct(int $id, ArticleInfo $info, string $img, string $figcaption, int $sequence)
    {
        $this->id = $id;
        $this->info = $info;
        $this->img = $img;
        $this->figcaption = $figcaption;
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
     * @return ArticleInfo
     */
    public function getInfo(): ArticleInfo
    {
        return $this->info;
    }

    /**
     * @param ArticleInfo $info
     */
    public function setInfo(ArticleInfo $info): void
    {
        $this->info = $info;
    }

    /**
     * @return string
     */
    public function getImg(): string
    {
        return $this->img;
    }

    /**
     * @param string $img
     */
    public function setImg(string $img): void
    {
        $this->img = $img;
    }

    /**
     * @return string
     */
    public function getFigcaption(): string
    {
        return $this->figcaption;
    }

    /**
     * @param string $figcaption
     */
    public function setFigcaption(string $figcaption): void
    {
        $this->figcaption = $figcaption;
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