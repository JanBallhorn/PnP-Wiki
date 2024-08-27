<?php

namespace App\Repository;

use App\Collection\SourceCollection;
use App\Model\Source;
use InvalidArgumentException;

class SourceRepository extends Repository implements RepositoryInterface
{
    private string $table = 'sources';
    public function findAll(string $order = ''): ?SourceCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    public function findById(int $id): ?Source
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    public function findBy(string $column, mixed $value, string $order = ''): ?SourceCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    public function findOneBy(string $column, mixed $value): ?Source
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof Source){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Source::class));
        }
        else{
            $this->connectDB();
            $id = $entity->getId();
            $name = $entity->getName();
            $type = $entity->getType();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (name, type) VALUES(?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ss", $name, $type);
        }
        else{
            $query = "UPDATE `$this->table` SET `name` = ?, `type` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssi", $name, $type, $id);
        }
        $stmt->execute();
        $this->closeDB();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof Source){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Source::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }
    private function findCollection(false|\mysqli_stmt $stmt): ?SourceCollection
    {
        $sources = new SourceCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($source = $result->fetch_object()) {
                $source = new Source($source->id, $source->name, $source->type);
                $sources->offsetSet($sources->key(), $source);
                $sources->next();
            }
            $this->closeDB();
            return $sources;
        }
        else {
            $this->closeDB();
            return null;
        }
    }
    private function findOne(false|\mysqli_result $result): ?Source
    {
        $source = $result->fetch_object();
        $this->closeDB();
        if(!empty($source)){
            return new Source($source->id, $source->name, $source->type);
        }
        else{
            return null;
        }
    }
}