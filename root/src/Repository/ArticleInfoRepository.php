<?php

namespace App\Repository;

use App\Collection\ArticleInfoCollection;
use App\Collection\ArticleInfoContentCollection;
use App\Collection\ArticleInfoGalleryCollection;
use App\Model\ArticleInfo;
use App\Model\ArticleInfoContent;
use App\Model\ArticleInfoGallery;
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

    /**
     * @throws Exception
     */
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
            $contents = $entity->getContent();
            $gallery = $entity->getGallery();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (article, headline) VALUES(?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("is", $article, $headline);
        }
        else{
            $query = "UPDATE `$this->table` SET `article` = ?, `headline` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isi", $article, $headline,  $id);
        }
        $success = $stmt->execute();
        if($success){
            $newArticleInfo = $id === 0;
            if(!$newArticleInfo){
                $id = $this->findOneBy('article', $article)->getId();
                $query = "DELETE FROM `article_info_contents` WHERE `info` = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $query = "DELETE FROM `article_info_gallery` WHERE `info` = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
            }
            else{
                $id = $this->db->insert_id;
            }
            $query = "INSERT INTO `article_info_contents` (info, topic, content, headline, sequence) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $contents->rewind();
            for($i = 1; $i <= $contents->count(); $i++){
                $content = $contents->current();
                $stmt->bind_param("isssi", $id, $content->getTopic(), $content->getContent(), $content->getHeadline(), $content->getSequence());
                $stmt->execute();
                $contents->next();
            }
            $query = "INSERT INTO `article_info_gallery` (info, img, figcaption, sequence) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $gallery->rewind();
            for($i = 1; $i <= $gallery->count(); $i++){
                $image = $gallery->current();
                $stmt->bind_param("issi", $id, $image->getImg(), $image->getFigcaption(), $i);
                $stmt->execute();
                $gallery->next();
            }
        }
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
                $info = new ArticleInfo($info->id, $info->article, $info->headline, $info->content, $info->gallery);
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
            return new ArticleInfo($info->id, $info->article, $info->headline, $info->content, $info->gallery);
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
        $info->content = $this->findContentsForInfo($info->id);
        $info->gallery = $this->findGalleryForInfo($info->id);
        $this->connectDB();
        return $info;
    }

    private function findContentsForInfo(int $infoId): ?ArticleInfoContentCollection
    {
        $this->connectDB();
        $contentIds = $this->findByFunc('article_info_contents', 'info', $infoId, 'id');
        $contents = new ArticleInfoContentCollection();
        $result = $contentIds->get_result();
        if ($result->num_rows > 0) {
            while ($content = $result->fetch_object()) {
                $content = new ArticleInfoContent($content->id, $content->topic, $content->content, $content->headline, $content->sequence);
                $contents->offsetSet($contents->key(), $content);
                $contents->next();
            }
            $this->closeDB();
            return $contents;
        }
        else {
            $this->closeDB();
            return null;
        }
    }

    private function findGalleryForInfo(int $infoId): ?ArticleInfoGalleryCollection
    {
        $this->connectDB();
        $galleryIds = $this->findByFunc('article_info_gallery', 'info', $infoId, 'sequence');
        $gallery = new ArticleInfoGalleryCollection();
        $result = $galleryIds->get_result();
        if ($result->num_rows > 0) {
            while ($img = $result->fetch_object()) {
                $img = new ArticleInfoGallery($img->id, $img->img, $img->figcaption, $img->sequence);
                $gallery->offsetSet($gallery->key(), $img);
                $gallery->next();
            }
            $this->closeDB();
            return $gallery;
        }
        else {
            $this->closeDB();
            return null;
        }
    }
}