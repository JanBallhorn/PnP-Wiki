<?php

namespace App\Model;

use App\Collection\ParagraphContentCollection;
use App\Repository\ParagraphContentRepository;
use App\Collection\ParagraphGalleryCollection;
use App\Repository\ParagraphGalleryRepository;
use DateTime;
use Exception;

class Paragraph
{
    private int $id;
    private DateTime $published;
    private User $createdBy;
    private DateTime $lastEdit;
    private User $lastEditBy;
    private Article $article;
    private string $headline;
    private int $sequence;

    /**
     * @param int $id
     * @param DateTime $published
     * @param User $createdBy
     * @param DateTime $lastEdit
     * @param User $lastEditBy
     * @param Article $article
     * @param string $headline
     * @param int $sequence
     */
    public function __construct(int $id, DateTime $published, User $createdBy, DateTime $lastEdit, User $lastEditBy, Article $article, string $headline, int $sequence)
    {
        $this->id = $id;
        $this->published = $published;
        $this->createdBy = $createdBy;
        $this->lastEdit = $lastEdit;
        $this->lastEditBy = $lastEditBy;
        $this->article = $article;
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
     * @return DateTime
     */
    public function getPublished(): DateTime
    {
        return $this->published;
    }

    /**
     * @param DateTime $published
     */
    public function setPublished(DateTime $published): void
    {
        $this->published = $published;
    }

    /**
     * @return User
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     */
    public function setCreatedBy(User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return DateTime
     */
    public function getLastEdit(): DateTime
    {
        return $this->lastEdit;
    }

    /**
     * @param DateTime $lastEdit
     */
    public function setLastEdit(DateTime $lastEdit): void
    {
        $this->lastEdit = $lastEdit;
    }

    /**
     * @return User
     */
    public function getLastEditBy(): User
    {
        return $this->lastEditBy;
    }

    /**
     * @param User $lastEditBy
     */
    public function setLastEditBy(User $lastEditBy): void
    {
        $this->lastEditBy = $lastEditBy;
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

    /**
     * @throws Exception
     */
    public function getContents(): ?ParagraphContentCollection
    {
        $contents = (new ParagraphContentRepository())->findBy('paragraph', $this->getId(), 'sequence');
        if($contents !== null){
            foreach ($contents as $content) {
                if($content->getImg() !== null) {
                    $image = dirname($_SERVER['DOCUMENT_ROOT']) . "/externalImages/" . $content->getImg();
                    if(!exif_imagetype($image)){
                        $imgType = "image/svg+xml";
                    }
                    else{
                        $imgType = image_type_to_mime_type(exif_imagetype($image));
                    }
                    $content->setImg("data:" . $imgType . ";base64," . base64_encode(file_get_contents($image)));
                    $contents->offsetSet($contents->key(), $content);
                }
            }
        }
        return $contents;
    }
}