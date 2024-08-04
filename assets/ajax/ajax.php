<?php

function dbConnect(){
    $host = "sql718.your-server.de";
    $username = "verpla_1";
    $password = "zUz5ffaPKifb711z";
    $dbname = "dsa_wiki";

    $conn = new mysqli($host, $username, $password, $dbname);
    if($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function checkDuplicate(string $field)
{
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT COUNT(`$field`) FROM `users` WHERE `$field` = ?");
    $stmt->bind_param("s", $_POST[$field]);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_row();
    $conn->close();
    return $result;
}

$key = array_keys($_POST)[0];
$data = $_POST;
if($key === 'email'){
    $result = checkDuplicate($key);
    if($result[0] > 0){
        echo json_encode(['exists' => true]);
    }
    else{
        echo json_encode(['exists' => false]);
    }
}
if($key === 'username'){
    $result = checkDuplicate($key);
    $usernameLength = strlen($data[$key]);
    $jsonArray = [];
    if($result[0] > 0){
        $jsonArray['exists'] = true;
    }
    else{
        $jsonArray['exists'] = false;
    }
    if($usernameLength > 3 || $data[$key] === ''){
        $jsonArray['length'] = true;
    }
    else{
        $jsonArray['length'] = false;
    }
    echo json_encode($jsonArray);
}
if($key === 'password'){
    $passwordLength = strlen($data[$key]);
    if($passwordLength > 5 || $data[$key] === ''){
        echo json_encode(['length' => true]);
    }
    else{
        echo json_encode(['length' => false]);
    }
}


