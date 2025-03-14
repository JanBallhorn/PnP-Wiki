<?php

namespace App\Repository;

use App\Database;
use mysqli;

abstract class Repository
{
    protected mysqli $db;

    protected function connectDB(): void
    {
        $this->db = Database::dbConnect();
    }

    protected function closeDB(): void
    {
        $this->db->close();
    }
    protected function findAllFunc(string $table, string $order): false|\mysqli_stmt
    {
        $this->connectDB();
        if(!empty($order)){
            $query = "SELECT * FROM `$table` ORDER BY ". $order;
        }
        else{
            $query = "SELECT * FROM `$table`";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    protected function findByIdFunc(string $table, int $id): false|\mysqli_result
    {
        $this->connectDB();
        $stmt = $this->db->prepare("SELECT * FROM `$table` WHERE `id` = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result();
    }
    protected function findByFunc(string $table, string $column, mixed $value, string $order): false|\mysqli_stmt
    {
        $this->connectDB();
        if(!empty($order) && $value !== null){
            $query = "SELECT * FROM `$table` WHERE `$column` = ? ORDER BY ". $order;
        }
        elseif(!empty($order) && $value === null){
            $query = "SELECT * FROM `$table` WHERE `$column` IS null ORDER BY " . $order;
        }
        elseif(empty($order) && $value === null){
            $query = "SELECT * FROM `$table` WHERE `$column` IS NULL";
        }
        else{
            $query = "SELECT * FROM `$table` WHERE `$column` = ?";
        }
        $stmt = $this->db->prepare($query);
        if($value === null){
            $stmt->execute();
        }
        else{
            $stmt->execute([$value]);
        }
        return $stmt;
    }
    protected function findOneByFunc(string $table, string $column, mixed $value): false|\mysqli_result
    {
        $this->connectDB();
        if($value === null){
            $query = "SELECT * FROM `$table` WHERE `$column` IS null";
        }
        else{
            $query = "SELECT * FROM `$table` WHERE `$column` = ?";
        }
        $stmt = $this->db->prepare($query);
        if($value === null){
            $stmt->execute();
        }
        else{
            $stmt->execute([$value]);
        }
        return $stmt->get_result();
    }
    protected function deleteFunc(string $table, object $entity): void
    {
        $this->connectDB();
        $id = $entity->getId();
        $query = "DELETE FROM `$table` WHERE `id` = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $this->closeDB();
    }
}