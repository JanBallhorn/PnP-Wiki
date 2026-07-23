<?php

namespace App\Repository;

use App\Collection\CategorySectionTemplateCollection;
use App\Model\CategorySectionTemplate;
use InvalidArgumentException;

class CategorySectionTemplateRepository extends Repository implements RepositoryInterface
{
    private string $table = 'category_section_template';

    public function __construct()
    {
        $this->connectDB();
    }

    public function findAll(string $order = ''): ?CategorySectionTemplateCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    public function findById(int $id): ?CategorySectionTemplate
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    public function findBy(string $column, mixed $value, string $order = ''): ?CategorySectionTemplateCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    public function findOneBy(string $column, mixed $value): ?CategorySectionTemplate
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof CategorySectionTemplate){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", CategorySectionTemplate::class));
        }
        $id = $entity->getId();
        $category = $entity->getCategory();
        $headline = $entity->getHeadline();
        $sequence = $entity->getSequence();
        if($id === 0){
            $query = "INSERT INTO `$this->table` (category, headline, sequence) VALUES(?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isi", $category, $headline, $sequence);
        }
        else{
            $query = "UPDATE `$this->table` SET `category` = ?, `headline` = ?, `sequence` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isii", $category, $headline, $sequence, $id);
        }
        $stmt->execute();
    }

    /**
     * Replaces the whole section template of a category in one go: drops the
     * existing rows and inserts the given set. Mirrors CategoryInfoTemplateRepository.
     */
    public function saveForCategory(int $category, CategorySectionTemplateCollection $rows): void
    {
        $query = "DELETE FROM `$this->table` WHERE `category` = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $category);
        $stmt->execute();
        $query = "INSERT INTO `$this->table` (category, headline, sequence) VALUES(?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $rows->rewind();
        for($i = 1; $i <= $rows->count(); $i++){
            $row = $rows->current();
            $headline = $row->getHeadline();
            $sequence = $row->getSequence();
            $stmt->bind_param("isi", $category, $headline, $sequence);
            $stmt->execute();
            $rows->next();
        }
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof CategorySectionTemplate){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", CategorySectionTemplate::class));
        }
        $this->deleteFunc($this->table, $entity);
    }

    private function findCollection(false|\mysqli_stmt $stmt): ?CategorySectionTemplateCollection
    {
        $rows = new CategorySectionTemplateCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_object()) {
                $row = new CategorySectionTemplate($row->id, $row->category, $row->headline, $row->sequence);
                $rows->offsetSet($rows->key(), $row);
                $rows->next();
            }
            return $rows;
        }
        return null;
    }

    private function findOne(false|\mysqli_result $result): ?CategorySectionTemplate
    {
        $row = $result->fetch_object();
        if(!empty($row)){
            return new CategorySectionTemplate($row->id, $row->category, $row->headline, $row->sequence);
        }
        return null;
    }
}
