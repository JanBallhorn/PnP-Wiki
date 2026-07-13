<?php

namespace App\Repository;


use App\Collection\ArticleCollection;
use App\Collection\CategoryCollection;
use App\Collection\ProjectCollection;
use App\Collection\UserCollection;
use App\Model\Article;
use App\Model\Category;
use App\Model\Project;
use DateMalformedStringException;
use DateTime;
use Exception;
use InvalidArgumentException;

class ArticleRepository extends Repository implements RepositoryInterface
{
    private string $table = 'articles';

    public function __construct()
    {
        $this->connectDB();
    }

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
    public function findAllBetween(int $start, int $limit, int $userId, string $order = 'articles.id', ?Category $categoryFilter = null, ?Project $projectFilter = null): ?ArticleCollection
    {
        //$query = "WITH T AS (SELECT *, (ROW_NUMBER() OVER (ORDER BY $order)) AS RN FROM `$this->table` WHERE private = 0 OR (private = 1 AND last_edit_by = $userId)) SELECT * FROM T WHERE RN BETWEEN $start AND $end";
        if (isset($categoryFilter)) {
            $query =
                "SELECT  DISTINCT `$this->table`.id 
                FROM `$this->table` 
                INNER JOIN `article_categories` 
                    ON `$this->table`.id = `article_categories`.`article`
                LEFT JOIN article_authorized 
                    ON `$this->table`.id = article_authorized.article 
                WHERE (private = 0 OR (private = 1 AND article_authorized.user = $userId))
                    AND `article_categories`.`category` =". $categoryFilter->getId() .
                " ORDER BY $order 
                LIMIT $limit 
                    OFFSET $start"
            ;
        } elseif (isset($projectFilter)) {
            $projectIds = $this->getProjectIdsWithChildren($projectFilter);
            $query =
                "SELECT  DISTINCT `$this->table`.id
                FROM `$this->table`
                LEFT JOIN article_authorized
                    ON `$this->table`.id = article_authorized.article
                WHERE (private = 0 OR (private = 1 AND article_authorized.user = $userId))
                    AND `$this->table`.project IN (" . implode(',', $projectIds) . ")
                ORDER BY $order
                LIMIT $limit
                    OFFSET $start"
            ;
        } else {
            $query =
                "SELECT  DISTINCT `$this->table`.id 
                FROM `$this->table` 
                LEFT JOIN article_authorized 
                    ON `$this->table`.id = article_authorized.article 
                WHERE private = 0 OR (private = 1 AND article_authorized.user = $userId) 
                ORDER BY $order 
                LIMIT $limit 
                    OFFSET $start"
            ;
        }
        $articles = new ArticleCollection();
        $articles->rewind();
        $this->findArticlesById($articles, $query);
        return $articles;
    }

    /**
     * @throws Exception
     */
    public function search(string $search, Category $category = null, Project $project = null): ArticleCollection
    {
        $projectIds = $project !== null ? $this->getProjectIdsWithChildren($project) : array();
        $articles = new ArticleCollection();
        $articles->rewind();
        $article= $this->findOneBy('headline', $search);
        if($article !== null){
            if($category !== null){
                $categories = $article->getCategories();
                $categories->rewind();
                for($i = 1; $i <= $categories->count(); $i++){
                    if($categories->current()->getId() === $category->getId()){
                        $articles->offsetSet($articles->key(), $article);
                        $articles->next();
                    }
                }
            }
            elseif(in_array($article->getProject()->getId(), $projectIds)){
                $articles->offsetSet($articles->key(), $article);
                $articles->next();
            }
            else{
                $articles->offsetSet($articles->key(), $article);
                $articles->next();
            }
        }
        $query =
            "SELECT articles.id
            FROM articles
            INNER JOIN article_alt_headline
                ON article_alt_headline.article = articles.id
            WHERE article_alt_headline.headline = ?"
        ;
        if($category !== null){
            $query =
                "SELECT articles.id
                FROM articles
                INNER JOIN article_alt_headline
                    ON article_alt_headline.article = articles.id
                INNER JOIN article_categories
                    ON articles.id = article_categories.article
                WHERE article_alt_headline.headline = ? AND category = " . $category->getId()
            ;
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute([$search]);
        $result = $stmt->get_result()->fetch_object();
        if(!empty($result)){
            $article = $this->findOneBy('id', $result->id);
            $articles->offsetSet($articles->key(), $article);
            $articles->next();
        }
        $query =
            "SELECT DISTINCT articles.id
            FROM articles
            INNER JOIN article_tags
                ON articles.id = article_tags.article
            WHERE tag = ?"
        ;
        if($category !== null){
            $query =
                "SELECT DISTINCT articles.id
                FROM articles
                INNER JOIN article_tags
                    ON articles.id = article_tags.article
                INNER JOIN article_categories
                    ON articles.id = article_categories.article
                WHERE tag = ? AND category = " . $category->getId()
            ;
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query, [$search]);
        $likeSearch = "%$search%";
        $query = "SELECT DISTINCT articles.id FROM articles WHERE headline LIKE ?";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_categories ON articles.id = article_categories.article WHERE headline LIKE ? AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query, [$likeSearch]);
        $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_alt_headline ON article_alt_headline.article = articles.id WHERE article_alt_headline.headline LIKE ?";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_alt_headline ON article_alt_headline.article = articles.id INNER JOIN article_categories ON articles.id = article_categories.article WHERE article_alt_headline.headline LIKE ? AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query, [$likeSearch]);
        $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_tags ON articles.id = article_tags.article WHERE tag LIKE ?";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_tags ON articles.id = article_tags.article INNER JOIN article_categories ON articles.id = article_categories.article WHERE tag LIKE ? AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query, [$likeSearch]);
        $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN paragraphs ON articles.id = paragraphs.article INNER JOIN paragraph_contents ON paragraphs.id = paragraph_contents.paragraph WHERE paragraph_contents.text LIKE ?";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN paragraphs ON articles.id = paragraphs.article INNER JOIN paragraph_contents ON paragraphs.id = paragraph_contents.paragraph INNER JOIN article_categories ON articles.id = article_categories.article WHERE paragraph_contents.text LIKE ? AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query .= " AND articles.project IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query, [$likeSearch]);
        $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_info ON articles.id = article_info.article INNER JOIN article_info_contents ON article_info.id = article_info_contents.info WHERE article_info.headline LIKE ? OR article_info_contents.headline LIKE ? OR article_info_contents.content LIKE ?";
        if($category !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_info ON articles.id = article_info.article INNER JOIN article_info_contents ON article_info.id = article_info_contents.info INNER JOIN article_categories ON articles.id = article_categories.article WHERE (article_info.headline LIKE ? OR article_info_contents.headline LIKE ? OR article_info_contents.content LIKE ?) AND category = " . $category->getId();
        }
        elseif ($project !== null){
            $query = "SELECT DISTINCT articles.id FROM articles INNER JOIN article_info ON articles.id = article_info.article INNER JOIN article_info_contents ON article_info.id = article_info_contents.info WHERE (article_info.headline LIKE ? OR article_info_contents.headline LIKE ? OR article_info_contents.content LIKE ?) AND articles.project  IN (" . implode(',', $projectIds) . ")";
        }
        $this->findArticlesById($articles, $query, [$likeSearch, $likeSearch, $likeSearch]);
        $articles->rewind();
        return $articles;
    }

    /**
     * @throws Exception
     */
    public function getNumberOfArticles(int $userId, ?Category $categoryFilter = null, ?Project $projectFilter = null): ?int
    {
        if (isset($categoryFilter)) {
            $query =
                "SELECT COUNT(articles.id) as num 
                FROM articles 
                INNER JOIN `article_categories` 
                ON `$this->table`.id = `article_categories`.`article`
                LEFT JOIN article_authorized 
                    ON articles.id = article_authorized.article 
                WHERE (private = 0 OR (private = 1 AND article_authorized.user = $userId))
                    AND `article_categories`.`category` = ".$categoryFilter->getId()
            ;
        } elseif (isset($projectFilter)) {
            $projectIds = $this->getProjectIdsWithChildren($projectFilter);
            $query =
                "SELECT COUNT(articles.id) as num
                FROM articles
                LEFT JOIN article_authorized
                    ON articles.id = article_authorized.article
                WHERE (private = 0 OR (private = 1 AND article_authorized.user = $userId))
                    AND `$this->table`.project IN (" . implode(',', $projectIds) . ")"
            ;
        } else {
            $query =
                "SELECT COUNT(articles.id) as num 
                FROM articles 
                LEFT JOIN article_authorized 
                    ON articles.id = article_authorized.article 
                WHERE private = 0 OR (private = 1 AND article_authorized.user = $userId)"
            ;
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_object();
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
            $authorized = $entity->getAuthorized();
            $editable = $entity->getEditable() === true ? 1 : 0;
            $called = $entity->getCalled();
            $empty = $entity->getEmpty() === true ? 1 : 0;
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (created_by, last_edit_by, headline, project, private, editable) VALUES(?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iisiii", $createdBy, $lastEditBy, $headline, $project, $private, $editable);
        }
        else{
            $query = "UPDATE `$this->table` SET `published` = ?, `created_by` = ?, `last_edit` = ?, `last_edit_by` = ?, `headline` = ?, `project` = ?, `private` = ?, `editable` = ?, `called` = ?, `empty` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sisisiiiiii", $published, $createdBy, $lastEdit, $lastEditBy, $headline, $project, $private, $editable, $called, $empty, $id);
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
                $query = "DELETE FROM `article_authorized` WHERE `article` = ?";
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
                    $tag = trim(strval($tags[$i]));
                    $stmt->bind_param("is", $id, $tag);
                    $stmt->execute();
                }
            }
            if(!empty($altHeadlines[0])){
                $query = "INSERT INTO `article_alt_headline` (headline, article) VALUES (?, ?)";
                $stmt = $this->db->prepare($query);
                for($i = 0; $i < count($altHeadlines); $i++){
                    $altHeadline = trim(strval($altHeadlines[$i]));
                    $stmt->bind_param("si", $altHeadline, $id);
                    $stmt->execute();
                }
            }
            if($authorized !== null){
                $query = "INSERT INTO `article_authorized` (article, user) VALUES (?, ?)";
                $stmt = $this->db->prepare($query);
                $authorized->rewind();
                for($i = 1; $i <= $authorized->count(); $i++){
                    $stmt->bind_param("ii", $id, $authorized->current()->getId());
                    $stmt->execute();
                    $authorized->next();
                }
            }
        }
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
                $article = new Article($article->id, $article->published, $article->created_by, $article->last_edit, $article->last_edit_by, $article->headline, $article->project, $article->categories, $article->tags, $article->altHeadlines, $article->private, $article->authorized, $article->editable, $article->called, $article->empty);
                $articles->offsetSet($articles->key(), $article);
                $articles->next();
            }
            return $articles;
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findOne(false|\mysqli_result $result): ?Article
    {
        $article = $result->fetch_object();
        if(!empty($article)){
            $article = $this->convertDataTypes($article);
            return new Article($article->id, $article->published, $article->created_by, $article->last_edit, $article->last_edit_by, $article->headline, $article->project, $article->categories, $article->tags, $article->altHeadlines, $article->private, $article->authorized, $article->editable, $article->called, $article->empty);
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
        $article->authorized =$this->findAuthorized($article->id);
        $article->editable = $article->editable === 1;
        $article->empty = $article->empty === 1;
        return $article;
    }

    /**
     * @throws DateMalformedStringException
     */
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
            return $categories;
        }
        else {
            return null;
        }
    }

    private function findTagsForArticle(int $articleId): ?array
    {
        $tagQuery = $this->findByFunc('article_tags', 'article', $articleId, '');
        $result = $tagQuery->get_result();
        if ($result->num_rows > 0) {
            $tags = array();
            while ($tag = $result->fetch_object()) {
                $tags[] = $tag->tag;
            }
            return $tags;
        }
        else {
            return null;
        }
    }

    private function findAltHeadlinesForArticle(int $articleId): ?array
    {
        $altHeadlineQuery = $this->findByFunc('article_alt_headline', 'article', $articleId, '');
        $result = $altHeadlineQuery->get_result();
        if ($result->num_rows > 0) {
            $altHeadlines = array();
            while ($altHeadline = $result->fetch_object()) {
                $altHeadlines[] = $altHeadline->headline;
            }
            return $altHeadlines;
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findAuthorized(int $articleId): ?UserCollection
    {
        $userIds = $this->findByFunc('article_authorized', 'article', $articleId, '');
        $users = new UserCollection();
        $result = $userIds->get_result();
        if ($result->num_rows > 0) {
            while ($user = $result->fetch_object()) {
                $user = (new UserRepository())->findById($user->user);
                $users->offsetSet($users->key(), $user);
                $users->next();
            }
            return $users;
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function findArticlesById(ArticleCollection $articles, string $query, array $params = []): void
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($id = $result->fetch_object()) {
                $article = $this->findOneBy('id', $id->id);
                $articles->offsetSet($articles->key(), $article);
                $articles->next();
            }
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

    /**
     * @return int[]
     */
    private function getProjectIdsWithChildren(Project $project): array
    {
        $projects = new ProjectCollection();
        $projects->rewind();
        $projects->offsetSet(0, $project);
        $projects->next();
        $projects = $this->findAllChildProjects($project, $projects);
        $projects->rewind();
        $projectIds = array();
        for($i = 0; $i < count($projects); $i++){
            $projectIds[] = $projects->current()->getId();
            $projects->next();
        }
        return $projectIds;
    }
}