<?php

namespace App;
use mysqli;

class Database
{
    public static function dbConnect(): mysqli
    {
        $conn = new mysqli("sql718.your-server.de", "verpla_1", "zUz5ffaPKifb711z", "dsa_wiki");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
}