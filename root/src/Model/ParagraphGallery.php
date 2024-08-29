<?php

namespace App\Model;

class ParagraphGallery
{
    private int $id;
    private ParagraphContent $paragraphContent;
    private string $img;
    private string $figcaption;
    private int $sequence;

    /**
     * @param int $id
     * @param ParagraphContent $paragraphContent
     * @param string $img
     * @param string $figcaption
     * @param int $sequence
     */
    public function __construct(int $id, ParagraphContent $paragraphContent, string $img, string $figcaption, int $sequence)
    {
        $this->id = $id;
        $this->paragraphContent = $paragraphContent;
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
     * @return ParagraphContent
     */
    public function getParagraphContent(): ParagraphContent
    {
        return $this->paragraphContent;
    }

    /**
     * @param ParagraphContent $paragraphContent
     */
    public function setParagraphContent(ParagraphContent $paragraphContent): void
    {
        $this->paragraphContent = $paragraphContent;
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