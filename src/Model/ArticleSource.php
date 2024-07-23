<?php

namespace App\Model;

class ArticleSource extends Model
{
    private int $id;
    private int $article;
    private int $source;
    private int $page;
    private string $link;

    /**
     * @param int $article
     * @param int $source
     * @param int $page
     * @param string $link
     */
    public function __construct(int $article, int $source, int $page, string $link)
    {
        $this->article = $article;
        $this->source = $source;
        $this->page = $page;
        $this->link = $link;
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
    public function getArticle(): int
    {
        return $this->article;
    }

    /**
     * @param int $article
     */
    public function setArticle(int $article): void
    {
        $this->article = $article;
    }

    /**
     * @return int
     */
    public function getSource(): int
    {
        return $this->source;
    }

    /**
     * @param int $source
     */
    public function setSource(int $source): void
    {
        $this->source = $source;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function create(): void
    {
        $conn = $this->dbConnect();
        $stmt = $conn->prepare("INSERT INTO `article_sources` (`article`, `source`, `page`, `link`)
        VALUES (?, ?, ?, ?)");
        $conn->execute_query($stmt, [$this->article, $this->source, $this->page, $this->link]);
        $this->closeConnection($conn);
    }
}