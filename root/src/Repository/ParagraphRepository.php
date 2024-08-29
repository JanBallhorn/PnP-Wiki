<?php

namespace App\Repository;

use App\Collection\ParagraphCollection;
use App\Model\Paragraph;
use DateTime;
use Exception;
use InvalidArgumentException;
use mysqli_result;
use mysqli_stmt;

class ParagraphRepository extends Repository implements RepositoryInterface
{
    private string $table = 'paragraphs';

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ParagraphCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?Paragraph
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ParagraphCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?Paragraph
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof Paragraph){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Paragraph::class));
        }
        else{
            $this->connectDB();
            $id = $entity->getId();
            $published  = date("Y-m-d H:i:s", $entity->getPublished()->getTimestamp());
            $createdBy = $entity->getCreatedBy()->getId();
            $lastEdit  = date("Y-m-d H:i:s");
            $lastEditBy = $entity->getLastEditBy()->getId();
            $article = $entity->getArticle()->getId();
            $headline = $entity->getHeadline();
            $sequence = $entity->getSequence();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (created_by, last_edit_by, article, headline, sequence) VALUES(?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssisi", $createdBy, $lastEditBy, $article, $headline, $sequence);
        }
        else{
            $query = "UPDATE `$this->table` SET `published` = ?, `created_by` = ?, `last_edit` = ?, `last_edit_by` = ?, `article` = ?, `headline` = ?, `sequence` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sisiisii", $published, $createdBy, $lastEdit, $lastEditBy, $article, $headline, $sequence, $id);
        }
        $stmt->execute();
        $this->closeDB();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof Paragraph){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Paragraph::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }
    /**
     * @param false|mysqli_result $result
     * @return Paragraph|null
     * @throws Exception
     */
    private function findOne(false|mysqli_result $result): ?Paragraph
    {
        $paragraph = $result->fetch_object();
        $this->closeDB();
        if (!empty($paragraph)) {
            $paragraph = $this->convertDataTypes($paragraph);
            return new Paragraph($paragraph->id, $paragraph->published, $paragraph->created_by, $paragraph->last_edit, $paragraph->last_edit_by, $paragraph->article, $paragraph->headline, $paragraph->sequence);
        } else {
            return null;
        }
    }

    /**
     * @param false|mysqli_stmt $stmt
     * @return ParagraphCollection|null
     * @throws Exception
     */
    private function findCollection(false|mysqli_stmt $stmt): ?ParagraphCollection
    {
        $paragraphs = new ParagraphCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($paragraph = $result->fetch_object()) {
                $paragraph = $this->convertDataTypes($paragraph);
                $paragraph = new Paragraph($paragraph->id, $paragraph->published, $paragraph->created_by, $paragraph->last_edit, $paragraph->last_edit_by, $paragraph->article, $paragraph->headline, $paragraph->sequence);
                $paragraphs->offsetSet($paragraphs->key(), $paragraph);
                $paragraphs->next();
            }
            $this->closeDB();
            return $paragraphs;
        }
        else {
            $this->closeDB();
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $paragraph): object{
        $paragraph->published = (new DateTime($paragraph->published));
        $paragraph->created_by = (new UserRepository())->findById($paragraph->created_by);
        $paragraph->last_edit = (new DateTime($paragraph->last_edit));
        $paragraph->last_edit_by = (new UserRepository())->findById($paragraph->last_edit_by);
        $paragraph->article = (new ArticleRepository())->findById($paragraph->article);
        $this->connectDB();
        return $paragraph;
    }
}