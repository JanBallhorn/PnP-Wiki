<?php

namespace App\Repository;

use App\Collection\UserCollection;
use App\Database;
use App\Model\User;
use http\Exception\InvalidArgumentException;
use mysqli;

class UserRepository implements RepositoryInterface
{
    private mysqli $db;
    private string $table = 'users';

    public function __construct(){
        $this->db = Database::dbConnect();
    }
    public function findAll(string $order = ''): UserCollection
    {
        $users = new UserCollection();
        if(!empty($order)){
            $query = "SELECT * FROM `$this->table` ORDER BY `$order` DESC";
        }
        else{
            $query = "SELECT * FROM `$this->table`";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        while($user = $result->fetch_object()){
            $user = new User($user->id, $user->firstname, $user->lastname, $user->email, $user->username, $user->password, $user->verified, $user->token, $user->firstname_public, $user->lastname_public, $user->profiletext);
            $users[] = $user;
        }
        return $users;
    }

    public function findById(int $id): User
    {
        $stmt = $this->db->prepare("SELECT * FROM `$this->table` WHERE `id` = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_object();
        return new User($user->id, $user->firstname, $user->lastname, $user->email, $user->username, $user->password, $user->verified, $user->token, $user->firstname_public, $user->lastname_public, $user->profiletext);
    }

    public function findBy(string $column, mixed $value, string $order = ''): UserCollection
    {
        $users = new UserCollection();
        if(!empty($order)){
            $query = "SELECT * FROM `$this->table` WHERE `$column` = ? ORDER BY `$order` DESC";
        }
        else{
            $query = "SELECT * FROM `$this->table` WHERE `$column` = ?";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute([$value]);
        $result = $stmt->get_result();
        while($user = $result->fetch_object()){
            $user = new User($user->id, $user->firstname, $user->lastname, $user->email, $user->username, $user->password, $user->verified, $user->token, $user->firstname_public, $user->lastname_public, $user->profiletext);
            $users[] = $user;
        }
        return $users;
    }
    public function findOneBy(string $column, mixed $value): ?User
    {
        $query = "SELECT * FROM `$this->table` WHERE `$column` = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$value]);
        $result = $stmt->get_result();
        $user = $result->fetch_object();
        if(!empty($user)) {
            return new User($user->id, $user->firstname, $user->lastname, $user->email, $user->username, $user->password, $user->verified, $user->token, $user->firstname_public, $user->lastname_public, $user->profiletext);
        }
        else{
            return null;
        }
    }

    public function save(object $entity): void
    {
        if(!$entity instanceof User){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", User::class));
        }
        else{
            $id = $entity->getId();
            $firstname = $entity->getFirstname();
            $lastname = $entity->getLastname();
            $email = $entity->getEmail();
            $username = $entity->getUsername();
            $password = $entity->getPassword();
            $verified = $entity->getVerified();
            $token = $entity->getToken();
            $firstnamePublic = $entity->getFirstnamePublic();
            $lastnamePublic = $entity->getLastnamePublic();
            $profiletext = $entity->getProfiletext();
            if($id === 0){
                $query = "INSERT INTO `$this->table` (firstname, lastname, email, username, password, verified, token) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("sssssis", $firstname, $lastname, $email, $username, $password, $verified, $token);
            }
            else{
                $query = "UPDATE `$this->table` SET `firstname` = ?, `lastname` = ?, `email` = ?, `username` = ?, `password` = ?, `verified` = ?, `token` = ?, `firstname_public` = ?, `lastname_public` = ?, `profiletext` = ? WHERE `id` = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("sssssisiisi", $firstname, $lastname, $email, $username, $password, $verified, $token, $firstnamePublic, $lastnamePublic, $profiletext, $id);
            }
            $stmt->execute();
        }
    }

    public function delete(object $entity): void
    {
        if(!$entity instanceof User){
            throw new InvalidArgumentException(sprintf("Entity must be instance of %s", User::class));
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
}