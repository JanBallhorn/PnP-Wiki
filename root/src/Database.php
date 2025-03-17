<?php

namespace App;
use mysqli;

class Database
{
    private static ?self $db = null;
    private mysqli $conn;

    private function __construct()
    {
        $location = dirname($_SERVER['DOCUMENT_ROOT']);
        $file = fopen($location . '/db_credentials.txt', 'r');
        $host = chop(fgets($file));
        $user = chop(fgets($file));
        $password = chop(fgets($file));
        $dbname = chop(fgets($file));
        fclose($file);
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