<?php

namespace App\Repository;

use App\Collection\ProjectCollection;
use App\Database;
use App\Model\Project;
use DateTime;
use http\Exception\InvalidArgumentException;
use mysqli;
use mysqli_result;
use mysqli_stmt;

class ProjectRepository implements RepositoryInterface
{
    private mysqli $db;
    private string $table = 'projects';

    public function __construct(){
        $this->db = Database::dbConnect();
    }
    public function findAll(string $order = ''): ProjectCollection
    {
        if(!empty($order)){
            $query = "SELECT * FROM `$this->table` ORDER BY `$order` DESC";
        }
        else{
            $query = "SELECT * FROM `$this->table`";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $this->findCollection($stmt);
    }

    public function findById(int $id): ?Project
    {
        $stmt = $this->db->prepare("SELECT * FROM `$this->table` WHERE `id` = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $this->findOne($result);
    }

    public function findBy(string $column, mixed $value, string $order = ''): ?ProjectCollection
    {
        if(!empty($order)){
            $query = "SELECT * FROM `$this->table` WHERE `$column` = ? ORDER BY `$order` DESC";
        }
        else{
            $query = "SELECT * FROM `$this->table` WHERE `$column` = ?";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute([$value]);
        return $this->findCollection($stmt);
    }

    public function findOneBy(string $column, mixed $value): ?Project
    {
        $stmt = $this->db->prepare("SELECT * FROM `$this->table` WHERE `$column` = ?");
        $stmt->execute([$value]);
        $result = $stmt->get_result();
        return $this->findOne($result);
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof Project){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Project::class));
        }
        else{
            $id = $entity->getId();
            $name = $entity->getName();
            $description = $entity->getDescription();
            $published  = $entity->getPublished()->getTimestamp();
            $createdBy = $entity->getCreatedBy()->getId();
            $lastEdit  = $entity->getLastEdit()->getTimestamp();
            $lastEditBy = $entity->getLastEditBy()->getId();
            $parentProject = $entity->getParentProject()->getId();
            $private = $entity->getPrivate() === true ? 1 : 0;
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (name, description, created_by, last_edit_by, parent_project, private) VALUES(?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("siiii", $name, $description, $createdBy, $lastEdit, $parentProject, $private);
        }
        else{
            $query = "UPDATE `$this->table` SET `name` = ?, `published` = ?, `created_by` = ?, `last_edit` = ?, `last_edit_by` = ?, `parent_project` = ?, `private` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("siiiiii", $name, $published, $createdBy, $lastEdit, $lastEditBy, $parentProject, $private, $id);
        }
        $stmt->execute();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof Project){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Project::class));
        }
        else{
            $id = $entity->getId();
            $query = "DELETE FROM `$this->table` WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }

    public function closeDB(): void
    {
        $this->db->close();
    }

    /**
     * @param false|mysqli_result $result
     * @return Project|null
     */
    private function findOne(false|mysqli_result $result): ?Project
    {
        $project = $result->fetch_object();
        if (!empty($project)) {
            $project = $this->convertDataTypes($project);
            return new Project($project->id, $project->name, $project->description, $project->published, $project->created_by, $project->last_edit, $project->last_edit_by, $project->parent_project, $project->private);
        } else {
            return null;
        }
    }

    /**
     * @param false|mysqli_stmt $stmt
     * @return ProjectCollection|null
     */
    private function findCollection(false|mysqli_stmt $stmt): ?ProjectCollection
    {
        $projects = new ProjectCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($project = $result->fetch_object()) {
                $project = $this->convertDataTypes($project);
                $project = new Project($project->id, $project->name, $project->description, $project->published, $project->created_by, $project->last_edit, $project->last_edit_by, $project->parent_project, $project->private);
                $projects[] = $project;
            }
            return $projects;
        } else {
            return null;
        }
    }
    private function convertDataTypes(object $project): object{
        $project->published = (new DateTime())->setTimestamp($project->published);
        $project->created_by = (new UserRepository())->findById($project->created_by);
        $project->last_edit = (new DateTime())->setTimestamp($project->last_edit);
        $project->last_edit_by = (new UserRepository())->findById($project->last_edit_by);
        $project->parent_project = $this->findById($project->parent_project);
        $project->private = $project->private === 1;
        return $project;
    }
}