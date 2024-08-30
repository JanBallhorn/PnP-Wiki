<?php

namespace App\Repository;

use App\Collection\ArticleSourceCollection;
use App\Model\ArticleSource;
use InvalidArgumentException;

class ArticleSourceRepository extends Repository implements RepositoryInterface
{
    private string $table = 'article_sources';
    public function findAll(string $order = ''): ?ArticleSourceCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    public function findById(int $id): ?ArticleSource
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    public function findBy(string $column, mixed $value, string $order = ''): ?ArticleSourceCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    public function findOneBy(string $column, mixed $value): ?ArticleSource
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof ArticleSource){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleSource::class));
        }
        else{
            $this->connectDB();
            $id = $entity->getId();
            $article = $entity->getArticle()->getId();
            $source = $entity->getSource()->getId();
            $page = $entity->getPage();
            $link = $entity->getLink();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (article, source, page, link) VALUES(?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iiss", $article, $source, $page, $link);
        }
        else{
            $query = "UPDATE `$this->table` SET `article` = ?, `source` = ?, `page` = ?, `link` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iissi", $article, $source, $page, $link, $id);
        }
        $stmt->execute();
        $this->closeDB();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof ArticleSource){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleSource::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    private function findCollection(false|\mysqli_stmt $stmt): ?ArticleSourceCollection
    {
        $articleSources = new ArticleSourceCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($articleSource = $result->fetch_object()) {
                $articleSource = $this->convertDataTypes($articleSource);
                $articleSource = new ArticleSource($articleSource->id, $articleSource->article, $articleSource->source, $articleSource->page, $articleSource->link);
                $articleSources->offsetSet($articleSources->key(), $articleSource);
                $articleSources->next();
            }
            $this->closeDB();
            return $articleSources;
        }
        else {
            $this->closeDB();
            return null;
        }
    }
    private function findOne(false|\mysqli_result $result): ?ArticleSource
    {
        $articleSource = $result->fetch_object();
        $this->closeDB();
        if(!empty($articleSource)){
            $articleSource = $this->convertDataTypes($articleSource);
            return new ArticleSource($articleSource->id, $articleSource->article, $articleSource->source, $articleSource->page, $articleSource->link);
        }
        else{
            return null;
        }
    }
    private function convertDataTypes(object $articleSource): object{
        $articleSource->article = (new ArticleRepository())->findById($articleSource->article);
        $articleSource->source = (new SourceRepository())->findById($articleSource->source);
        $this->connectDB();
        return $articleSource;
    }
}