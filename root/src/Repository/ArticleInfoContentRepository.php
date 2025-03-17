<?php

namespace App\Repository;

use App\Collection\ArticleInfoContentCollection;
use App\Model\ArticleInfoContent;
use Exception;
use InvalidArgumentException;

class ArticleInfoContentRepository extends Repository implements RepositoryInterface
{
    private string $table = 'article_info_contents';

    public function __construct()
    {
        $this->connectDB();
    }

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ArticleInfoContentCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?ArticleInfoContent
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ArticleInfoContentCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?ArticleInfoContent
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof ArticleInfoContent){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleInfoContent::class));
        }
        else{
            $id = $entity->getId();
            $topic = $entity->getTopic();
            $content = $entity->getContent();
            $headline = $entity->getHeadline();
            $sequence = $entity->getSequence();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (info, topic, content, headline, sequence) VALUES(?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isssi", $info, $topic, $content, $headline, $sequence);
        }
        else{
            $query = "UPDATE `$this->table` SET `info` = ?, `topic` = ?, `content` = ?, `headline` = ?, `sequence` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isssii", $info, $topic, $content, $headline, $sequence, $id);
        }
        $stmt->execute();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof ArticleInfoContent){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleInfoContent::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @throws Exception
     */
    private function findCollection(false|\mysqli_stmt $stmt): ?ArticleInfoContentCollection
    {
        $infoContents = new ArticleInfoContentCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($infoContent = $result->fetch_object()) {
                $infoContent = $this->convertDataTypes($infoContent);
                $infoContent = new ArticleInfoContent($infoContent->id, $infoContent->topic, $infoContent->content, $infoContent->headline, $infoContent->sequence);
                $infoContents->offsetSet($infoContents->key(), $infoContent);
                $infoContents->next();
            }
            return $infoContent;
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findOne(false|\mysqli_result $result): ?ArticleInfoContent
    {
        $infoContent = $result->fetch_object();
        if(!empty($infoContent)){
            $infoContent = $this->convertDataTypes($infoContent);
            return new ArticleInfoContent($infoContent->id, $infoContent->topic, $infoContent->content, $infoContent->headline, $infoContent->sequence);
        }
        else{
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $infoContent): object{
        $infoContent->info = (new ArticleInfoRepository())->findById($infoContent->info);
        return $infoContent;
    }
}