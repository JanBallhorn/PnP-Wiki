<?php

namespace App;
use mysqli;

class Database
{
    private static ?self $db = null;
    private mysqli $conn;

    private function __construct()
    {
        $host = Env::getRequired('DB_HOST');
        $user = Env::getRequired('DB_USER');
        $password = Env::getRequired('DB_PASSWORD');
        $dbname = Env::getRequired('DB_NAME');
        $this->conn = new mysqli($host, $user, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    public static function dbConnect(): Database
    {
        if(self::$db === null) {
            self::$db = new Database();
        }
        return self::$db;
    }
    public function getConnection(): mysqli
    {
        return $this->conn;
    }
}