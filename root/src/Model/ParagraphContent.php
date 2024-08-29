<?php

namespace App\Model;

class ParagraphContent
{
    private int $id;
    private Paragraph $paragraph;
    private string $text;
    private string $img;
    private string $figcaption;
    private bool $gallery;
    private int $sequence;

    /**
     * @param int $id
     * @param Paragraph $paragraph
     * @param string $text
     * @param string $img
     * @param string $figcaption
     * @param bool $gallery
     * @param int $sequence
     */
    public function __construct(int $id, Paragraph $paragraph, string $text, string $img, string $figcaption, bool $gallery, int $sequence)
    {
        $this->id = $id;
        $this->paragraph = $paragraph;
        $this->text = $text;
        $this->img = $img;
        $this->figcaption = $figcaption;
        $this->gallery = $gallery;
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
     * @return Paragraph
     */
    public function getParagraph(): Paragraph
    {
        return $this->paragraph;
    }

    /**
     * @param Paragraph $paragraph
     */
    public function setParagraph(Paragraph $paragraph): void
    {
        $this->paragraph = $paragraph;
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
     * @return bool
     */
    public function getGallery(): bool
    {
        return $this->gallery;
    }

    /**
     * @param bool $gallery
     */
    public function setGallery(bool $gallery): void
    {
        $this->gallery = $gallery;
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