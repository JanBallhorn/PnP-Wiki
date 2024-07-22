<?php

namespace App\Model;

class ParagraphGallery extends Model
{
    private int $id;
    private int $paragraphContentId;
    private string $img;
    private string $figcaption;
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
    public function getParagraphContentId(): int
    {
        return $this->paragraphContentId;
    }

    /**
     * @param int $paragraphContentId
     */
    public function setParagraphContentId(int $paragraphContentId): void
    {
        $this->paragraphContentId = $paragraphContentId;
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