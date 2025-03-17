<?php

namespace App\Repository;

use App\Collection\ArticleSourceCollection;
use App\Model\ArticleSource;
use Exception;
use InvalidArgumentException;

class ArticleSourceRepository extends Repository implements RepositoryInterface
{
    private string $table = 'article_sources';

    public function __construct()
    {
        $this->connectDB();
    }

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ArticleSourceCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?ArticleSource
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ArticleSourceCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
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
            $id = $entity->getId();
            $article = $entity->getArticle()->getId();
            $source = $entity->getSource()->getId();
            $reference = $entity->getReference();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (article, source, reference) VALUES(?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iis", $article, $source, $reference);
        }
        else{
            $query = "UPDATE `$this->table` SET `article` = ?, `source` = ?, `reference` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iisi", $article, $source, $reference, $id);
        }
        $stmt->execute();
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

    /**
     * @throws Exception
     */
    private function findCollection(false|\mysqli_stmt $stmt): ?ArticleSourceCollection
    {
        $articleSources = new ArticleSourceCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($articleSource = $result->fetch_object()) {
                $articleSource = $this->convertDataTypes($articleSource);
                $articleSource = new ArticleSource($articleSource->id, $articleSource->article, $articleSource->source, $articleSource->reference);
                $articleSources->offsetSet($articleSources->key(), $articleSource);
                $articleSources->next();
            }
            return $articleSources;
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findOne(false|\mysqli_result $result): ?ArticleSource
    {
        $articleSource = $result->fetch_object();
        if(!empty($articleSource)){
            $articleSource = $this->convertDataTypes($articleSource);
            return new ArticleSource($articleSource->id, $articleSource->article, $articleSource->source, $articleSource->reference);
        }
        else{
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $articleSource): object{
        $articleSource->article = (new ArticleRepository())->findById($articleSource->article);
        $articleSource->source = (new SourceRepository())->findById($articleSource->source);
        return $articleSource;
    }
}