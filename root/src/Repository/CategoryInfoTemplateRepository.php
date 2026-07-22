<?php

namespace App\Repository;

use App\Collection\CategoryInfoTemplateCollection;
use App\Model\CategoryInfoTemplate;
use InvalidArgumentException;

class CategoryInfoTemplateRepository extends Repository implements RepositoryInterface
{
    private string $table = 'category_info_template';

    public function __construct()
    {
        $this->connectDB();
    }

    public function findAll(string $order = ''): ?CategoryInfoTemplateCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    public function findById(int $id): ?CategoryInfoTemplate
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    public function findBy(string $column, mixed $value, string $order = ''): ?CategoryInfoTemplateCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    public function findOneBy(string $column, mixed $value): ?CategoryInfoTemplate
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof CategoryInfoTemplate){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", CategoryInfoTemplate::class));
        }
        $id = $entity->getId();
        $category = $entity->getCategory();
        $headline = $entity->getHeadline();
        $topic = $entity->getTopic();
        $sequence = $entity->getSequence();
        if($id === 0){
            $query = "INSERT INTO `$this->table` (category, headline, topic, sequence) VALUES(?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("issi", $category, $headline, $topic, $sequence);
        }
        else{
            $query = "UPDATE `$this->table` SET `category` = ?, `headline` = ?, `topic` = ?, `sequence` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("issii", $category, $headline, $topic, $sequence, $id);
        }
        $stmt->execute();
    }

    /**
     * Replaces the whole template of a category in one go: drops the existing
     * rows and inserts the given set. Mirrors how ArticleInfoRepository rewrites
     * an infobox's contents on save.
     */
    public function saveForCategory(int $category, CategoryInfoTemplateCollection $rows): void
    {
        $query = "DELETE FROM `$this->table` WHERE `category` = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $category);
        $stmt->execute();
        $query = "INSERT INTO `$this->table` (category, headline, topic, sequence) VALUES(?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $rows->rewind();
        for($i = 1; $i <= $rows->count(); $i++){
            $row = $rows->current();
            $headline = $row->getHeadline();
            $topic = $row->getTopic();
            $sequence = $row->getSequence();
            $stmt->bind_param("issi", $category, $headline, $topic, $sequence);
            $stmt->execute();
            $rows->next();
        }
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof CategoryInfoTemplate){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", CategoryInfoTemplate::class));
        }
        $this->deleteFunc($this->table, $entity);
    }

    private function findCollection(false|\mysqli_stmt $stmt): ?CategoryInfoTemplateCollection
    {
        $rows = new CategoryInfoTemplateCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_object()) {
                $row = new CategoryInfoTemplate($row->id, $row->category, $row->headline, $row->topic, $row->sequence);
                $rows->offsetSet($rows->key(), $row);
                $rows->next();
            }
            return $rows;
        }
        return null;
    }

    private function findOne(false|\mysqli_result $result): ?CategoryInfoTemplate
    {
        $row = $result->fetch_object();
        if(!empty($row)){
            return new CategoryInfoTemplate($row->id, $row->category, $row->headline, $row->topic, $row->sequence);
        }
        return null;
    }
}
