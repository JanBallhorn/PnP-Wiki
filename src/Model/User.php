<?php

namespace App\Model;

class User
{
    private int $id;
    private string $firstname;
    private string $lastname;
    private string $email;
    private string $username;
    private string $password;
    private int $verified;
    private string $token;
    private int $firstnamePublic;
    private int $lastnamePublic;
    private string $profiletext;

    /**
     * @param int $id
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $username
     * @param string $password
     * @param int $verified
     * @param string $token
     * @param int $firstnamePublic
     * @param int $lastnamePublic
     * @param string $profiletext
     */
    public function __construct(int $id, string $firstname, string $lastname, string $email, string $username, string $password, int $verified, string $token, int $firstnamePublic, int $lastnamePublic, string $profiletext)
    {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->username = $username;
        $this->password = $password;
        $this->verified = $verified;
        $this->token = $token;
        $this->firstnamePublic = $firstnamePublic;
        $this->lastnamePublic = $lastnamePublic;
        $this->profiletext = $profiletext;
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

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getFirstnamePublic(): int
    {
        return $this->firstnamePublic;
    }

    /**
     * @param int $firstnamePublic
     */
    public function setFirstnamePublic(int $firstnamePublic): void
    {
        $this->firstnamePublic = $firstnamePublic;
    }

    /**
     * @return int
     */
    public function getLastnamePublic(): int
    {
        return $this->lastnamePublic;
    }

    /**
     * @param int $lastnamePublic
     */
    public function setLastnamePublic(int $lastnamePublic): void
    {
        $this->lastnamePublic = $lastnamePublic;
    }

    /**
     * @return string
     */
    public function getProfiletext(): string
    {
        return $this->profiletext;
    }

    /**
     * @param string $profiletext
     */
    public function setProfiletext(string $profiletext): void
    {
        $this->profiletext = $profiletext;
    }

}