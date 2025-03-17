<?php

namespace App\Repository;

use App\Collection\CategoryCollection;
use App\Model\Category;
use DateMalformedStringException;
use DateTime;
use Exception;
use InvalidArgumentException;

class CategoryRepository extends Repository implements RepositoryInterface
{
    private string $table = 'categories';

    public function __construct()
    {
        $this->connectDB();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function findAll(string $order = ''): ?CategoryCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function findById(int $id): ?Category
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?CategoryCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function findOneBy(string $column, mixed $value): ?Category
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function findAllBetween(int $start, int $end, string $order = 'id'): ?CategoryCollection
    {
        $query = "WITH T AS (SELECT *, (ROW_NUMBER() OVER (ORDER BY $order)) AS RN FROM `$this->table`) SELECT * FROM T WHERE RN BETWEEN $start AND $end";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $this->findCollection($stmt);
    }

    public function findPopularCategories(): ?CategoryCollection
    {
        $query = "SELECT COUNT(category) AS Sum, categories.id FROM `article_categories` RIGHT JOIN categories ON article_categories.category = categories.id GROUP BY categories.id ORDER BY Sum DESC, name LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $categories = new CategoryCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($category = $result->fetch_object()) {
                $category = $this->findOneBy("id", $category->id);
                $categories->offsetSet($categories->key(), $category);
                $categories->next();
            }
            return $categories;
        }
        else {
            return null;
        }
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof Category){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Category::class));
        }
        else{
            $id = $entity->getId();
            $name = $entity->getName();
            $description = $entity->getDescription();
            $published  = date("Y-m-d H:i:s", $entity->getPublished()->getTimestamp());
            $createdBy = $entity->getCreatedBy()->getId();
            $lastEdit  = date("Y-m-d H:i:s");
            $lastEditBy = $entity->getLastEditBy()->getId();
            $icon = $entity->getIcon();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (name, description, created_by, last_edit_by, icon) VALUES(?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssiis", $name, $description, $createdBy, $lastEditBy, $icon);
        }
        else{
            $query = "UPDATE `$this->table` SET `name` = ?, `description` = ?, `published` = ?, `created_by` = ?, `last_edit` = ?, `last_edit_by` = ?, `icon` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssisisi", $name, $description, $published, $createdBy, $lastEdit, $lastEditBy, $icon, $id);
        }
        $stmt->execute();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof Category){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Category::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    private function findCollection(false|\mysqli_stmt $stmt): ?CategoryCollection
    {
        $categories = new CategoryCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($category = $result->fetch_object()) {
                $category = $this->convertDataTypes($category);
                $category = new Category($category->id, $category->name, $category->description, $category->published, $category->created_by, $category->last_edit, $category->last_edit_by, $category->icon);
                $categories->offsetSet($categories->key(), $category);
                $categories->next();
            }
            return $categories;
        }
        else {
            return null;
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    private function findOne(false|\mysqli_result $result): ?Category
    {
        $category = $result->fetch_object();
        if(!empty($category)){
            $category = $this->convertDataTypes($category);
            return new Category($category->id, $category->name, $category->description, $category->published, $category->created_by, $category->last_edit, $category->last_edit_by, $category->icon);
        }
        else{
            return null;
        }
    }

    /**
     * @throws DateMalformedStringException
     * @throws Exception
     */
    private function convertDataTypes(object $category): object{
        $category->published = (new DateTime($category->published));
        $category->created_by = (new UserRepository())->findById($category->created_by);
        $category->last_edit = (new DateTime($category->last_edit));
        $category->last_edit_by = (new UserRepository())->findById($category->last_edit_by);
        return $category;
    }
}