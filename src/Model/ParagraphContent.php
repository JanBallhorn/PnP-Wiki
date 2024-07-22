<?php

namespace App\Model;

class ParagraphContent extends Model
{
    private int $id;
    private int $paragraphId;
    private string $text;
    private string $img;
    private string $figcaption;
    private int $gallery;
    private int $order;

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
    public function getParagraphId(): int
    {
        return $this->paragraphId;
    }

    /**
     * @param int $paragraphId
     */
    public function setParagraphId(int $paragraphId): void
    {
        $this->paragraphId = $paragraphId;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
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
    public function getGallery(): int
    {
        return $this->gallery;
    }

    /**
     * @param int $gallery
     */
    public function setGallery(int $gallery): void
    {
        $this->gallery = $gallery;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }
}