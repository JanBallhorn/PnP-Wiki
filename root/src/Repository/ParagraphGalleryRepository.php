<?php

namespace App\Repository;

use App\Collection\ParagraphGalleryCollection;
use App\Model\ParagraphGallery;
use Exception;
use InvalidArgumentException;
use mysqli_result;
use mysqli_stmt;

class ParagraphGalleryRepository extends Repository implements RepositoryInterface
{
    private string $table = 'paragraph_gallery';

    public function __construct()
    {
        $this->connectDB();
    }

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ParagraphGalleryCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?ParagraphGallery
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ParagraphGalleryCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?ParagraphGallery
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof ParagraphGallery){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ParagraphGallery::class));
        }
        else{
            $id = $entity->getId();
            $paragraphContent = $entity->getParagraphContent()->getId();
            $img = $entity->getImg();
            $figcaption = $entity->getFigcaption();
            $sequence = $entity->getSequence();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (paragraph_content, img, figcaption, sequence) VALUES(?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("issi", $paragraphContent, $img, $figcaption, $sequence);
        }
        else{
            $query = "UPDATE `$this->table` SET `paragraph_content` = ?, `img` = ?, `figcaption` = ?, `sequence` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("issii", $paragraphContent, $img, $figcaption, $sequence, $id);
        }
        $stmt->execute();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof ParagraphGallery){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ParagraphGallery::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @param false|mysqli_result $result
     * @return ParagraphGallery|null
     * @throws Exception
     */
    private function findOne(false|mysqli_result $result): ?ParagraphGallery
    {
        $paragraphGallery = $result->fetch_object();
        if (!empty($paragraphGallery)) {
            $paragraphGallery = $this->convertDataTypes($paragraphGallery);
            return new ParagraphGallery($paragraphGallery->id, $paragraphGallery->paragraph_content, $paragraphGallery->img, $paragraphGallery->figcaption, $paragraphGallery->sequence);
        } else {
            return null;
        }
    }

    /**
     * @param false|mysqli_stmt $stmt
     * @return ParagraphGalleryCollection|null
     * @throws Exception
     */
    private function findCollection(false|mysqli_stmt $stmt): ?ParagraphGalleryCollection
    {
        $paragraphGalleries = new ParagraphGalleryCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($paragraphGallery = $result->fetch_object()) {
                $paragraphGallery = $this->convertDataTypes($paragraphGallery);
                $paragraphGallery = new ParagraphGallery($paragraphGallery->id, $paragraphGallery->paragraph_content, $paragraphGallery->img, $paragraphGallery->figcaption, $paragraphGallery->sequence);
                $paragraphGalleries->offsetSet($paragraphGalleries->key(), $paragraphGallery);
                $paragraphGalleries->next();
            }
            return $paragraphGalleries;
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $paragraphGallery): object{
        $paragraphGallery->paragraph_content = (new ParagraphContentRepository())->findById($paragraphGallery->paragraph_content);
        return $paragraphGallery;
    }
}