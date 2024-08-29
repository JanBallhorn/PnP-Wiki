<?php

namespace App\Repository;

use App\Collection\ParagraphContentCollection;
use App\Model\ParagraphContent;
use Exception;
use InvalidArgumentException;
use mysqli_result;
use mysqli_stmt;

class ParagraphContentRepository extends Repository implements RepositoryInterface
{
    private string $table = 'paragraph_contents';

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ParagraphContentCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?ParagraphContent
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ParagraphContentCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?ParagraphContent
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof ParagraphContent){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ParagraphContent::class));
        }
        else{
            $this->connectDB();
            $id = $entity->getId();
            $paragraph = $entity->getParagraph()->getId();
            $text = $entity->getText();
            $img = $entity->getImg();
            $figcaption = $entity->getFigcaption();
            $gallery = $entity->getGallery() === true ? 1 : 0;
            $sequence = $entity->getSequence();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (paragraph, text, img, figcaption, gallery, sequence) VALUES(?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isssii", $paragraph, $text, $img, $figcaption, $gallery, $sequence);
        }
        else{
            $query = "UPDATE `$this->table` SET `paragraph` = ?, `text` = ?, `img` = ?, `figcaption` = ?, `gallery` = ?, `sequence` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isssiii", $paragraph, $text, $img, $figcaption, $gallery, $sequence, $id);
        }
        $stmt->execute();
        $this->closeDB();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof ParagraphContent){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ParagraphContent::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @param false|mysqli_result $result
     * @return ParagraphContent|null
     * @throws Exception
     */
    private function findOne(false|mysqli_result $result): ?ParagraphContent
    {
        $paragraphContent = $result->fetch_object();
        $this->closeDB();
        if (!empty($paragraphContent)) {
            $paragraphContent = $this->convertDataTypes($paragraphContent);
            return new ParagraphContent($paragraphContent->id, $paragraphContent->paragraph, $paragraphContent->text, $paragraphContent->img, $paragraphContent->figcaption, $paragraphContent->gallery, $paragraphContent->sequence);
        } else {
            return null;
        }
    }

    /**
     * @param false|mysqli_stmt $stmt
     * @return ParagraphContentCollection|null
     * @throws Exception
     */
    private function findCollection(false|mysqli_stmt $stmt): ?ParagraphContentCollection
    {
        $paragraphContents = new ParagraphContentCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($paragraphContent = $result->fetch_object()) {
                $paragraphContent = $this->convertDataTypes($paragraphContent);
                $paragraphContent = new ParagraphContent($paragraphContent->id, $paragraphContent->paragraph, $paragraphContent->text, $paragraphContent->img, $paragraphContent->figcaption, $paragraphContent->gallery, $paragraphContent->sequence);
                $paragraphContents->offsetSet($paragraphContents->key(), $paragraphContent);
                $paragraphContents->next();
            }
            $this->closeDB();
            return $paragraphContents;
        }
        else {
            $this->closeDB();
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $paragraphContent): object{
        $paragraphContent->paragraph = (new ParagraphRepository())->findById($paragraphContent->paragraph);
        $paragraphContent->gallery = $paragraphContent->gallery === 1;
        $this->connectDB();
        return $paragraphContent;
    }
}