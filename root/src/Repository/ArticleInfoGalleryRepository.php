<?php

namespace App\Repository;

use App\Collection\ArticleInfoGalleryCollection;
use App\Model\ArticleInfoGallery;
use Exception;
use InvalidArgumentException;

class ArticleInfoGalleryRepository extends Repository implements RepositoryInterface
{
    private string $table = 'article_info_gallery';

    public function __construct()
    {
        $this->connectDB();
    }

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ArticleInfoGalleryCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?ArticleInfoGallery
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ArticleInfoGalleryCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?ArticleInfoGallery
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof ArticleInfoGallery){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleInfoGallery::class));
        }
        else{
            $id = $entity->getId();
            $img = $entity->getImg();
            $figcaption = $entity->getFigcaption();
            $sequence = $entity->getSequence();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (info, img, figcaption, sequence) VALUES(?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("issi", $info, $img, $figcaption, $sequence);
        }
        else{
            $query = "UPDATE `$this->table` SET `info` = ?, `img` = ?, `figcaption` = ?, `sequence` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("issii", $info, $img, $figcaption, $sequence, $id);
        }
        $stmt->execute();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof ArticleInfoGallery){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleInfoGallery::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @throws Exception
     */
    private function findCollection(false|\mysqli_stmt $stmt): ?ArticleInfoGalleryCollection
    {
        $infoGalleries = new ArticleInfoGalleryCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($infoGallery = $result->fetch_object()) {
                $infoGallery = $this->convertDataTypes($infoGallery);
                $infoGallery = new ArticleInfoGallery($infoGallery->id, $infoGallery->img, $infoGallery->figcaption, $infoGallery->sequence);
                $infoGalleries->offsetSet($infoGalleries->key(), $infoGallery);
                $infoGalleries->next();
            }
            return $infoGalleries;
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findOne(false|\mysqli_result $result): ?ArticleInfoGallery
    {
        $infoGallery = $result->fetch_object();
        if(!empty($infoGallery)){
            $infoGallery = $this->convertDataTypes($infoGallery);
            return new ArticleInfoGallery($infoGallery->id, $infoGallery->img, $infoGallery->figcaption, $infoGallery->sequence);
        }
        else{
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $infoGallery): object{
        $infoGallery->info = (new ArticleInfoRepository())->findById($infoGallery->info);
        return $infoGallery;
    }
}