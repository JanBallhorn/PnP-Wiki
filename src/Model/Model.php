<?php

namespace App\Model;

use mysqli;

abstract class Model
{
    private string $host = "sql718.your-server.de";
    private string $username = "verpla_1";
    private string $password = "zUz5ffaPKifb711z";
    private string $dbname = "dsa_wiki";

    public function dbConnect(){
        $conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        if($conn->connect_error){
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
    public function closeConnection($conn): void
    {
        $conn->close();
    }
    public function findById(int $id, string $table): array|false
    {
        $conn = $this->dbConnect();
        $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->bind_param("si", $table, $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $this->closeConnection($conn);
        return $result;
    }
    public function findAll(string $table): array
    {
        $conn = $this->dbConnect();
        $stmt = $conn->prepare("SELECT * FROM $table");
        $stmt->bind_param("s", $table);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $this->closeConnection($conn);
        return $result;
    }
    public function findBy(string $field, string $value, string $table): array|false
    {
        $conn = $this->dbConnect();
        $stmt = $conn->prepare("SELECT * FROM $table WHERE $field = ?");
        if(gettype($value) == "string"){
            $stmt->bind_param("s", $value);
        }
        else{
            $stmt->bind_param("i", $value);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $this->closeConnection($conn);
        return $result;
    }
    public function defineTypes(array $values): string{
        $types = "";
        foreach($values as $value){
            if(gettype($value) == "string"){
                $types .= "s";
            }
            else{
                $types .= "i";
            }
        }
        return $types;
    }
}