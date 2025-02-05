<?php

namespace App;
use mysqli;

class Database
{
    public static function dbConnect(): mysqli
    {
        $location = dirname($_SERVER['DOCUMENT_ROOT']);
        $file = fopen($location . '/db_credentials.txt', 'r');
        $host = chop(fgets($file));
        $user = chop(fgets($file));
        $password = chop(fgets($file));
        $dbname = chop(fgets($file));
        fclose($file);
        $conn = new mysqli($host, $user, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
}