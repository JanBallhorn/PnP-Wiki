<?php

namespace App\Model;

class User extends Model
{
    private int $id;
    private string $firstname;
    private string $lastname;
    private string $email;
    private string $username;
    private string $password;
    private int $verified;

    /**
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $username
     * @param string $password
     * @param int $verified
     */
    public function __construct(string $firstname, string $lastname, string $email, string $username, string $password, int $verified)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->username = $username;
        $this->password = $password;
        $this->verified = $verified;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return int
     */
    public function getVerified(): int
    {
        return $this->verified;
    }

    /**
     * @param int $verified
     */
    public function setVerified(int $verified): void
    {
        $this->verified = $verified;
    }

    public function create(): void
    {
        $conn = $this->dbConnect();
        $stmt = "INSERT INTO `users` (`firstname`, `lastname`, `email`, `username`, `password`, `verified`) VALUES (?, ?, ?, ?, ?, ?)";
        $conn->execute_query($stmt, [$this->firstname, $this->lastname, $this->email, $this->username, $this->password, $this->verified]);
        $this->closeConnection($conn);
    }
}