<?php

namespace App\Repository;

use App\Collection\ArticleInfoCollection;
use App\Model\ArticleInfo;
use Exception;
use InvalidArgumentException;

class ArticleInfoRepository extends Repository implements RepositoryInterface
{
    private string $table = 'article_info';

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ArticleInfoCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?ArticleInfo
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ArticleInfoCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?ArticleInfo
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof ArticleInfo){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleInfo::class));
        }
        else{
            $this->connectDB();
            $id = $entity->getId();
            $article = $entity->getArticle()->getId();
            $headline = $entity->getHeadline();
            $img = $entity->getImg();
            $figcaption = $entity->getFigcaption();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (article, headline, img, figcaption) VALUES(?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isss", $article, $headline, $img, $figcaption);
        }
        else{
            $query = "UPDATE `$this->table` SET `article` = ?, `headline` = ?, `img` = ?, `figcaption` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isssi", $article, $headline, $img, $figcaption, $id);
        }
        $stmt->execute();
        $this->closeDB();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof ArticleInfo){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", ArticleInfo::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @throws Exception
     */
    private function findCollection(false|\mysqli_stmt $stmt): ?ArticleInfoCollection
    {
        $infos = new ArticleInfoCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($info = $result->fetch_object()) {
                $info = $this->convertDataTypes($info);
                $info = new ArticleInfo($info->id, $info->article, $info->headline, $info->img, $info->figcaption);
                $infos->offsetSet($infos->key(), $info);
                $infos->next();
            }
            $this->closeDB();
            return $info;
        }
        else {
            $this->closeDB();
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findOne(false|\mysqli_result $result): ?ArticleInfo
    {
        $info = $result->fetch_object();
        $this->closeDB();
        if(!empty($info)){
            $info = $this->convertDataTypes($info);
            return new ArticleInfo($info->id, $info->article, $info->headline, $info->img, $info->figcaption);
        }
        else{
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $info): object{
        $info->article = (new ArticleRepository())->findById($info->article);
        $this->connectDB();
        return $info;
    }
}