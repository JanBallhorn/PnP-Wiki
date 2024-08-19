<?php /** @noinspection ALL */

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

function checkDuplicate(string $field, string $value, string $table): bool
{
    $conn = dbConnect();
    $stmt = $conn->prepare("SELECT COUNT(`$field`) FROM `$table` WHERE `$field` = ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_row();
    $conn->close();
    return $result[0] ? true : false;
}

if($_POST['errorType'] === 'duplicate'){
    $result = checkDuplicate($_POST['field'], $_POST['value'], $_POST['table']);
    echo json_encode(['duplicate' => $result]);
}