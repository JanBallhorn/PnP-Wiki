<?php

namespace App\Repository;

use App\Collection\ArticleListCollection;
use App\Model\ArticleList;
use DateTime;
use Exception;
use InvalidArgumentException;

class ArticleListRepository extends Repository implements RepositoryInterface
{
    private string $table = 'lists';

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ArticleListCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?ArticleList
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ArticleListCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?ArticleList
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof ArticleList){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleList::class));
        }
        else{
            $this->connectDB();
            $id = $entity->getId();
            $category = $entity->getCategory()->getId();
            $published  = date("Y-m-d H:i:s", $entity->getPublished()->getTimestamp());
            $createdBy = $entity->getCreatedBy()->getId();
            $lastEdit  = date("Y-m-d H:i:s");
            $lastEditBy = $entity->getLastEditBy()->getId();
            $name = $entity->getName();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (category, created_by, last_edit_by, name) VALUES(?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssiis", $category, $createdBy, $lastEditBy, $name);

        }
        else{
            $query = "UPDATE `$this->table` SET `category` = ?, `published` = ?, `created_by` = ?, `last_edit` = ?, `last_edit_by` = ?, `name` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssisisi", $category, $published, $createdBy, $lastEdit, $lastEditBy, $name, $id);
        }
        $stmt->execute();
        $this->closeDB();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof ArticleList){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleList::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @throws Exception
     */
    private function findCollection(false|\mysqli_stmt $stmt): ?ArticleListCollection
    {
        $lists = new ArticleListCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($list = $result->fetch_object()) {
                $list = $this->convertDataTypes($list);
                $list = new ArticleList($list->id, $list->category, $list->published, $list->created_by, $list->last_edit, $list->last_edit_by, $list->name);
                $lists->offsetSet($lists->key(), $list);
                $lists->next();
            }
            $this->closeDB();
            return $lists;
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findOne(false|\mysqli_result $result): ?ArticleList
    {
        $list = $result->fetch_object();
        $this->closeDB();
        if(!empty($list)){
            $list = $this->convertDataTypes($list);
            return new ArticleList($list->id, $list->category, $list->published, $list->created_by, $list->last_edit, $list->last_edit_by, $list->name);
        }
        else{
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $list): object
    {
        $list->category = (new CategoryRepository())->findById($list->category);
        $list->published = (new DateTime($list->published));
        $list->created_by = (new UserRepository())->findById($list->created_by);
        $list->last_edit = (new DateTime($list->last_edit));
        $list->last_edit_by = (new UserRepository())->findById($list->last_edit_by);
        $this->connectDB();
        return $list;
    }
}