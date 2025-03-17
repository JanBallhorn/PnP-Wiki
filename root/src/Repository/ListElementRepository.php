<?php

namespace App\Repository;

use App\Collection\ListElementCollection;
use App\Model\ListElement;
use Exception;
use InvalidArgumentException;

class ListElementRepository extends Repository implements RepositoryInterface
{
    private string $table = 'list_elements';

    public function __construct()
    {
        $this->connectDB();
    }

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ListElementCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?ListElement
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ListElementCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?ListElement
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof ListElement){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ListElement::class));
        }
        else{
            $id = $entity->getId();
            $list = $entity->getList()->getId();
            $article = $entity->getArticle()->getId();
            $name = $entity->getName();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (list, article, element_name) VALUES(?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iis", $list, $article, $name);
        }
        else{
            $query = "UPDATE `$this->table` SET `list` = ?, `article` = ?, `element_name` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iisi", $list, $article, $name, $id);
        }
        $stmt->execute();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof ListElement){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ListElement::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @throws Exception
     */
    private function findCollection(false|\mysqli_stmt $stmt): ?ListElementCollection
    {
        $elements = new ListElementCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($element = $result->fetch_object()) {
                $element = $this->convertDataTypes($element);
                $element = new ListElement($element->id, $element->list, $element->article, $element->name);
                $elements->offsetSet($elements->key(), $element);
                $elements->next();
            }
            return $elements;
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findOne(false|\mysqli_result $result): ?ListElement
    {
        $element = $result->fetch_object();
        if(!empty($element)){
            $element = $this->convertDataTypes($element);
            return new ListElement($element->id, $element->list, $element->article, $element->name);
        }
        else{
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $element): object
    {
        $element->list = (new ArticleListRepository())->findById($element->list);
        $element->article = (new ArticleRepository())->findById($element->article);
        return $element;
    }
}