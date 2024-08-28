<?php

namespace App\Repository;

use App\Collection\ArticleInfoContentCollection;
use App\Model\ArticleInfo;
use App\Model\ArticleInfoContent;
use InvalidArgumentException;

class ArticleInfoContentRepository extends Repository implements RepositoryInterface
{
    private string $table = 'article_info_contents';
    public function findAll(string $order = ''): ?ArticleInfoContentCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    public function findById(int $id): ?ArticleInfoContent
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    public function findBy(string $column, mixed $value, string $order = ''): ?ArticleInfoContentCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

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
            $this->connectDB();
            $id = $entity->getId();
            $info = $entity->getInfo()->getId();
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
        $this->closeDB();
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
    private function findCollection(false|\mysqli_stmt $stmt): ?ArticleInfoContentCollection
    {
        $infoContents = new ArticleInfoContentCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($infoContent = $result->fetch_object()) {
                $infoContent = $this->convertDataTypes($infoContent);
                $infoContent = new ArticleInfoContent($infoContent->id, $infoContent->info, $infoContent->topic, $infoContent->content, $infoContent->headline, $infoContent->sequence);
                $infoContents->offsetSet($infoContents->key(), $infoContent);
                $infoContents->next();
            }
            $this->closeDB();
            return $infoContent;
        }
        else {
            $this->closeDB();
            return null;
        }
    }
    private function findOne(false|\mysqli_result $result): ?ArticleInfoContent
    {
        $infoContent = $result->fetch_object();
        $this->closeDB();
        if(!empty($source)){
            $infoContent = $this->convertDataTypes($infoContent);
            return new ArticleInfoContent($infoContent->id, $infoContent->info, $infoContent->topic, $infoContent->content, $infoContent->headline, $infoContent->sequence);
        }
        else{
            return null;
        }
    }
    private function convertDataTypes(object $infoContent): object{
        $infoContent->info = (new ArticleInfoRepository())->findById($infoContent->info);
        $this->connectDB();
        return $infoContent;
    }
}