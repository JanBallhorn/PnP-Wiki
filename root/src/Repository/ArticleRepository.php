<?php

namespace App\Repository;


use App\Collection\ArticleCollection;
use App\Collection\CategoryCollection;
use App\Model\Article;
use DateTime;
use Exception;
use InvalidArgumentException;

class ArticleRepository extends Repository implements RepositoryInterface
{
    private string $table = 'articles';

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ArticleCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?Article
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ArticleCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?Article
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    /**
     * @throws Exception
     */
    public function save(object $entity): void
    {
        if(!$entity instanceof Article){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Article::class));
        }
        else{
            $this->connectDB();
            $id = $entity->getId();
            $published = date("Y-m-d H:i:s", $entity->getPublished()->getTimestamp());
            $createdBy = $entity->getCreatedBy()->getId();
            $lastEdit = date("Y-m-d H:i:s", $entity->getLastEdit()->getTimestamp());
            $lastEditBy = $entity->getLastEditBy()->getId();
            $headline = $entity->getHeadline();
            $project = $entity->getProject()->getId();
            $categories = $entity->getCategories();
            $private = $entity->getPrivate() === true ? 1 : 0;
            $editable = $entity->getEditable() === true ? 1 : 0;
            $called = $entity->getCalled();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (created_by, last_edit_by, headline, project, private, editable) VALUES(?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iisiii", $createdBy, $lastEditBy, $headline, $project, $private, $editable);
        }
        else{
            $query = "UPDATE `$this->table` SET `published` = ?, `created_by` = ?, `last_edit` = ?, `last_edit_by` = ?, `headline` = ?, `project` = ?, `private` = ?, `editable` = ?, `called` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sisisiiiii", $published, $createdBy, $lastEdit, $lastEditBy, $headline, $project, $private, $editable, $called, $id);
        }
        $success = $stmt->execute();
        if($success){
            $newArticle = $id === 0;
            $id = $this->findOneBy('headline', $headline)->getId();
            if(!$newArticle){
                $query = "DELETE FROM `article_categories` WHERE `article` = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
            }
            $query = "INSERT INTO `article_categories` (article, category) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            for($i = 1; $i <= $categories->count(); $i++){
                $stmt->bind_param("ii", $id, $categories->current()->getId());
                $stmt->execute();
                $categories->next();
            }
        }
        $this->closeDB();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof Article){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Article::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @throws Exception
     */
    private function findCollection(false|\mysqli_stmt $stmt): ?ArticleCollection
    {
        $articles = new ArticleCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($article = $result->fetch_object()) {
                $article = $this->convertDataTypes($article);
                $article = new Article($article->id, $article->published, $article->created_by, $article->last_edit, $article->last_edit_by, $article->headline, $article->project, $article->categories, $article->private, $article->editable, $article->called);
                $articles->offsetSet($articles->key(), $article);
                $articles->next();
            }
            $this->closeDB();
            return $articles;
        }
        else {
            $this->closeDB();
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findOne(false|\mysqli_result $result): ?Article
    {
        $article = $result->fetch_object();
        $this->closeDB();
        if(!empty($source)){
            $article = $this->convertDataTypes($article);
            return new Article($article->id, $article->published, $article->created_by, $article->last_edit, $article->last_edit_by, $article->headline, $article->project, $article->categories, $article->private, $article->editable, $article->called);
        }
        else{
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $article): object{
        $article->published = (new DateTime($article->published));
        $article->created_by = (new UserRepository())->findById($article->created_by);
        $article->last_edit = (new DateTime($article->last_edit));
        $article->last_edit_by = (new UserRepository())->findById($article->last_edit_by);
        $article->project = (new ProjectRepository())->findById($article->project);
        $article->categories = $this->findCategoriesForArticle($article->id);
        $article->private = $article->private === 1;
        $article->editable = $article->editable === 1;
        $this->connectDB();
        return $article;
    }
    private function findCategoriesForArticle(int $articleId): ?CategoryCollection
    {
        $categoryIds = $this->findByFunc('article_categories', 'article', $articleId, '');
        $categories = new CategoryCollection();
        $result = $categoryIds->get_result();
        if ($result->num_rows > 0) {
            while ($category = $result->fetch_object()) {
                $category = (new CategoryRepository())->findById($category->category);
                $categories->offsetSet($categories->key(), $category);
                $categories->next();
            }
            $this->closeDB();
            return $category;
        }
        else {
            $this->closeDB();
            return null;
        }
    }

}