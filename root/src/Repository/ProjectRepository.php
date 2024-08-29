<?php

namespace App\Repository;

use App\Collection\ProjectCollection;
use App\Model\Project;
use DateTime;
use Exception;
use http\Exception\InvalidArgumentException;
use mysqli_result;
use mysqli_stmt;

class ProjectRepository extends Repository implements RepositoryInterface
{
    private string $table = 'projects';

    /**
     * @throws Exception
     */
    public function findAll(string $order = ''): ?ProjectCollection
    {
        return $this->findCollection($this->findAllFunc($this->table, $order));
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?Project
    {
        return $this->findOne($this->findByIdFunc($this->table, $id));
    }

    /**
     * @throws Exception
     */
    public function findBy(string $column, mixed $value, string $order = ''): ?ProjectCollection
    {
        return $this->findCollection($this->findByFunc($this->table, $column, $value, $order));
    }

    /**
     * @throws Exception
     */
    public function findOneBy(string $column, mixed $value): ?Project
    {
        return $this->findOne($this->findOneByFunc($this->table, $column, $value));
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof Project){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Project::class));
        }
        else{
            $this->connectDB();
            $id = $entity->getId();
            $name = $entity->getName();
            $description = $entity->getDescription();
            $published  = date("Y-m-d H:i:s", $entity->getPublished()->getTimestamp());
            $createdBy = $entity->getCreatedBy()->getId();
            $lastEdit  = date("Y-m-d H:i:s");
            $lastEditBy = $entity->getLastEditBy()->getId();
            $parentProject = $entity->getParentProject()?->getId();
            $private = $entity->getPrivate() === true ? 1 : 0;
            $searched = $entity->getSearched();
        }
        if($id === 0){
            $query = "INSERT INTO `$this->table` (name, description, created_by, last_edit_by, parent_project, private) VALUES(?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssiiii", $name, $description, $createdBy, $lastEditBy, $parentProject, $private);
        }
        else{
            $query = "UPDATE `$this->table` SET `name` = ?, `description` = ?, `published` = ?, `created_by` = ?, `last_edit` = ?, `last_edit_by` = ?, `parent_project` = ?, `private` = ?, `searched` = ? WHERE `id` = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssisiiiii", $name, $description, $published, $createdBy, $lastEdit, $lastEditBy, $parentProject, $private, $searched, $id);
        }
        $stmt->execute();
        $this->closeDB();
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof Project){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", Project::class));
        }
        else{
            $this->deleteFunc($this->table, $entity);
        }
    }

    /**
     * @param false|mysqli_result $result
     * @return Project|null
     * @throws Exception
     */
    private function findOne(false|mysqli_result $result): ?Project
    {
        $project = $result->fetch_object();
        $this->closeDB();
        if (!empty($project)) {
            $project = $this->convertDataTypes($project);
            return new Project($project->id, $project->name, $project->description, $project->published, $project->created_by, $project->last_edit, $project->last_edit_by, $project->parent_project, $project->private, $project->searched);
        } else {
            return null;
        }
    }

    /**
     * @param false|mysqli_stmt $stmt
     * @return ProjectCollection|null
     * @throws Exception
     */
    private function findCollection(false|mysqli_stmt $stmt): ?ProjectCollection
    {
        $projects = new ProjectCollection();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($project = $result->fetch_object()) {
                $project = $this->convertDataTypes($project);
                $project = new Project($project->id, $project->name, $project->description, $project->published, $project->created_by, $project->last_edit, $project->last_edit_by, $project->parent_project, $project->private, $project->searched);
                $projects->offsetSet($projects->key(), $project);
                $projects->next();
            }
            $this->closeDB();
            return $projects;
        }
        else {
            $this->closeDB();
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function convertDataTypes(object $project): object{
        $project->published = (new DateTime($project->published));
        $project->created_by = (new UserRepository())->findById($project->created_by);
        $project->last_edit = (new DateTime($project->last_edit));
        $project->last_edit_by = (new UserRepository())->findById($project->last_edit_by);
        if($project->parent_project !== null){
            $project->parent_project = $this->findById($project->parent_project);
        }
        $project->private = $project->private === 1;
        $this->connectDB();
        return $project;
    }
}