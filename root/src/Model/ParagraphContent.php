<?php

namespace App\Model;

use App\Collection\ParagraphGalleryCollection;
use App\Repository\ParagraphGalleryRepository;
use Exception;

class ParagraphContent
{
    private int $id;
    private Paragraph $paragraph;
    private string|null $text;
    private string|null $img;
    private string|null $figcaption;
    private bool $gallery;
    private int $sequence;

    /**
     * @param int $id
     * @param Paragraph $paragraph
     * @param string|null $text
     * @param string|null $img
     * @param string|null $figcaption
     * @param bool $gallery
     * @param int $sequence
     */
    public function __construct(int $id, Paragraph $paragraph, string|null $text, string|null $img, string|null $figcaption, bool $gallery, int $sequence)
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
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $text
     */
    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return string|null
     */
    public function getImg(): ?string
    {
        return $this->img;
    }

    /**
     * @param string|null $img
     */
    public function setImg(?string $img): void
    {
        $this->img = $img;
    }

    /**
     * @return string|null
     */
    public function getFigcaption(): ?string
    {
        return $this->figcaption;
    }

    /**
     * @param string|null $figcaption
     */
    public function setFigcaption(?string $figcaption): void
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



    /**
     * @throws Exception
     */
    public function getGalleryImages(): ?ParagraphGalleryCollection
    {
        $galleries = (new ParagraphGalleryRepository())->findBy('paragraph_content', $this->getId(), 'sequence');
        foreach ($galleries as $gallery) {
            $image = dirname($_SERVER['DOCUMENT_ROOT']) . "/externalImages/" . $gallery->getImg();
            if(!exif_imagetype($image)){
                $imgType = "image/svg+xml";
            }
            else{
                $imgType = image_type_to_mime_type(exif_imagetype($image));
            }
            $gallery->setImg("data:" . $imgType . ";base64," . base64_encode(file_get_contents($image)));
            $galleries->offsetSet($galleries->key(), $gallery);
        }
        return $galleries;
    }

}