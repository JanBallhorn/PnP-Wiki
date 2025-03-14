<?php

namespace App\Repository;


use App\Collection\ArticleCollection;
use App\Collection\CategoryCollection;
use App\Collection\ProjectCollection;
use App\Model\Article;
use App\Model\Category;
use App\Model\Project;
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
    public function findAllBetween(int $start, int $end, int $userId, string $order = 'id'): ?ArticleCollection
    {
        $this->connectDB();
        $query = "WITH T AS (SELECT *, (ROW_NUMBER() OVER (ORDER BY $order)) AS RN FROM `$this->table` WHERE private = 0 OR (private = 1 AND last_edit_by = $userId)) SELECT * FROM T WHERE RN BETWEEN $start AND $end";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $this->findCollection($stmt);
    }

    /**
     * @throws Exception
     */
    public function search(string $search, Category $category = null, Project $project = null): ArticleCollection
    {
        $projects = new ProjectCollection();
        $projectIds = array();
        if($project !== null){
            $projects->rewind();
            $projects->offsetSet(0, $project);
            $projects->next();
            $projects = $this->findAllChildProjects($project, $projects);
            $projects->rewind();
            for($i = 0; $i < count($projects); $i++){
                $projectIds[] = $projects->current()->getId();
                $projects->next();
            }
        }
        $this->connectDB();
        $articles = new ArticleCollection();
        $article= $this->findOneBy('headline', $search);
        if($article !== null){
            if($category !== null){
                $categories = $article->getCategories();
                for($i = 1; $i <= $categories->count(); $i++){
                    if($categories->current()->getId() === $category->getId()){
                        $articles->offsetSet($articles->current(), $article);
                        $articles->next();
                    }
                }
            }
            elseif(in_array($article->getProject()->getId(), $projectIds)){
                $articles->offsetSet($articles->current(), $article);
                $articles->next();
            }
            else{
                $articles->offsetSet($articles->current(), $article);
                $articles->next();
            }
        }
        $query = "SELECT articles.id FROM articles INNER JOIN article_alt_headline ON article_alt_headline.article = articles.id WHERE article_alt_headline.headline = '$search'";
        if($category !== null){
            $query = "SELECT articles.id FROM articles INNER JOIN article_alt_headline ON article_alt_headline.article = articles.id INNER JOIN article_categories ON articles.id = article_categories.article WHERE article_alt_headline.headline = '$search' AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->connectDB();
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_object();
        if(!empty($result)){
            $article = $this->findOneBy('id', $result->id);
            $articles->offsetSet($articles->current(), $article);
            $articles->next();
        }
        $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_tags ON articles.id = article_tags.article WHERE tag = '$search'";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_tags ON articles.id = article_tags.article INNER JOIN article_categories ON articles.id = article_categories.article WHERE tag = '$search' AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query);
        $query = "SELECT * FROM articles WHERE headline LIKE '%$search%'";
        if($category !== null){
            $query = "SELECT * FROM articles INNER JOIN article_categories ON articles.id = article_categories.article WHERE headline LIKE '%$search%' AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->connectDB();
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $this->findCollection($stmt);
        if($result !== null){
            $result->rewind();
            for($i = 0; $i < $result->count(); $i++){
                $articles->offsetSet($articles->key(), $result->current());
                $articles->next();
                $result->next();
            }
        }
        $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_alt_headline ON article_alt_headline.article = articles.id WHERE article_alt_headline.headline LIKE '%$search%'";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_alt_headline ON article_alt_headline.article = articles.id INNER JOIN article_categories ON articles.id = article_categories.article WHERE article_alt_headline.headline LIKE '%$search%' AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query);
        $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_tags ON articles.id = article_tags.article WHERE tag LIKE '%$search%'";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_tags ON articles.id = article_tags.article INNER JOIN article_categories ON articles.id = article_categories.article WHERE tag LIKE '%$search%' AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query);
        $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN paragraphs ON articles.id = paragraphs.article INNER JOIN paragraph_contents ON paragraphs.id = paragraph_contents.paragraph WHERE paragraph_contents.text LIKE '%$search%'";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN paragraphs ON articles.id = paragraphs.article INNER JOIN paragraph_contents ON paragraphs.id = paragraph_contents.paragraph INNER JOIN article_categories ON articles.id = article_categories.article WHERE paragraph_contents.text LIKE '%$search%' AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query);
        $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_info ON articles.id = article_info.article INNER JOIN article_info_contents ON article_info.id = article_info_contents.info WHERE article_info.headline LIKE '%$search%' OR article_info_contents.headline LIKE '%$search%' OR article_info_contents.content LIKE '%$search%'";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_info ON articles.id = article_info.article INNER JOIN article_info_contents ON article_info.id = article_info_contents.info INNER JOIN article_categories ON articles.id = article_categories.article WHERE (article_info.headline LIKE '%$search%' OR article_info_contents.headline LIKE '%$search%' OR article_info_contents.content LIKE '%$search%') AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_info ON articles.id = article_info.article INNER JOIN article_info_contents ON article_info.id = article_info_contents.info WHERE (article_info.headline LIKE '%$search%' OR article_info_contents.headline LIKE '%$search%' OR article_info_contents.content LIKE '%$search%') AND articles.project  IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query);
        $this->closeDB();
        $articles->rewind();
        return $articles;
    }

    public function getNumberOfArticles(int $userId): ?int{
        $query = "SELECT COUNT(id) as num FROM articles WHERE private = 0 OR (private = 1 AND last_edit = '$userId')";
        $this->connectDB();
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_object();
        $this->closeDB();
        if(!empty($result)){
            return $result->num;
        }
        return null;
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
            $tags = $entity->getTags();
            $altHeadlines = $entity->getAltHeadlines();
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
            if(!$newArticle){
                $id = $this->findOneBy('headline', $headline)->getId();
                $query = "DELETE FROM `article_categories` WHERE `article` = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $query = "DELETE FROM `article_tags` WHERE `article` = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $query = "DELETE FROM `article_alt_headline` WHERE `article` = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
            }
            else{
                $id = $this->db->insert_id;
            }
            $query = "INSERT INTO `article_categories` (article, category) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            $categories->rewind();
            for($i = 1; $i <= $categories->count(); $i++){
                $stmt->bind_param("ii", $id, $categories->current()->getId());
                $stmt->execute();
                $categories->next();
            }
            if(!empty($tags[0])){
                $query = "INSERT INTO `article_tags` (article, tag) VALUES (?, ?)";
                $stmt = $this->db->prepare($query);
                for($i = 0; $i < count($tags); $i++){
                    $tag = strval($tags[$i]);
                    $stmt->bind_param("is", $id, $tag);
                    $stmt->execute();
                }
            }
            if(!empty($altHeadlines[0])){
                $query = "INSERT INTO `article_alt_headline` (headline, article) VALUES (?, ?)";
                $stmt = $this->db->prepare($query);
                for($i = 0; $i < count($altHeadlines); $i++){
                    $altHeadline = strval($altHeadlines[$i]);
                    $stmt->bind_param("si", $altHeadline, $id);
                    $stmt->execute();
                }
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
                $article = new Article($article->id, $article->published, $article->created_by, $article->last_edit, $article->last_edit_by, $article->headline, $article->project, $article->categories, $article->tags, $article->altHeadlines, $article->private, $article->editable, $article->called);
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
        if(!empty($article)){
            $article = $this->convertDataTypes($article);
            return new Article($article->id, $article->published, $article->created_by, $article->last_edit, $article->last_edit_by, $article->headline, $article->project, $article->categories, $article->tags, $article->altHeadlines, $article->private, $article->editable, $article->called);
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
        $article->tags = $this->findTagsForArticle($article->id);
        $article->altHeadlines = $this->findAltHeadlinesForArticle($article->id);
        $article->private = $article->private === 1;
        $article->editable = $article->editable === 1;
        $this->connectDB();
        return $article;
    }
    private function findCategoriesForArticle(int $articleId): ?CategoryCollection
    {
        $this->connectDB();
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
            return $categories;
        }
        else {
            $this->closeDB();
            return null;
        }
    }
    private function findTagsForArticle(int $articleId): ?array
    {
        $this->connectDB();
        $tagQuery = $this->findByFunc('article_tags', 'article', $articleId, '');
        $result = $tagQuery->get_result();
        if ($result->num_rows > 0) {
            $tags = array();
            while ($tag = $result->fetch_object()) {
                $tags[] = $tag->tag;
            }
            $this->closeDB();
            return $tags;
        }
        else {
            $this->closeDB();
            return null;
        }
    }
    private function findAltHeadlinesForArticle(int $articleId): ?array
    {
        $this->connectDB();
        $altHeadlineQuery = $this->findByFunc('article_alt_headline', 'article', $articleId, '');
        $result = $altHeadlineQuery->get_result();
        if ($result->num_rows > 0) {
            $altHeadlines = array();
            while ($altHeadline = $result->fetch_object()) {
                $altHeadlines[] = $altHeadline->headline;
            }
            $this->closeDB();
            return $altHeadlines;
        }
        else {
            $this->closeDB();
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findArticlesById(ArticleCollection $articles, string $query): void
    {
        $this->connectDB();
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($id = $result->fetch_object()) {
                $article = $this->findOneBy('id', $id->id);
                $articles->offsetSet($articles->key(), $article);
                $articles->next();
            }
            $this->closeDB();
        }
    }

    /**
     * @throws Exception
     */
    private function findAllChildProjects(Project $project, ProjectCollection $projects): ProjectCollection
    {
        $childProjects = $project->getChildren();
        if($childProjects !== null){
            $childProjects->rewind();
            for($i = 1; $i <= $childProjects->count(); $i++){
                $projects->offsetSet($projects->key(), $childProjects->current());
                $projects->next();
                if($childProjects->current()->getChildren() !== null){
                    $projects = $this->findAllChildProjects($childProjects->current(), $projects);
                }
                $childProjects->next();
            }
        }
        return $projects;
    }
}