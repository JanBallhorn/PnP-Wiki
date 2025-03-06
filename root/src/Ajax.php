<?php

namespace App;
use App\Database;
use mysqli;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/../inc/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';
class Ajax
{
    protected mysqli $db;
    protected function connectDB(): void
    {
        $this->db = Database::dbConnect();
    }
    protected function closeDB(): void
    {
        $this->db->close();
    }

    function checkDuplicate(string $field, string $value, string $table): bool
    {
        $this->connectDB();
        $query = "SELECT COUNT(`$field`) FROM `$table` WHERE `$field` = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
        $this->closeDB();
        return (bool)$result[0];
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    function ajaxRender($template, $data): string
    {
        $loader = new FilesystemLoader(__DIR__ . '/Views');
        $twig = new Environment($loader, ['debug' => true]);
        return $twig->render($template, $data);
    }

    function trackVisits(string $article): void
    {
        $this->connectDB();
        $query = "UPDATE `articles` SET `called` = `called` + 1 WHERE `headline` = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $article);
        $stmt->execute();
        $this->closeDB();
    }
}


if(isset($_POST['errorType']) && $_POST['errorType'] === 'duplicate'){
    $result = (new Ajax())->checkDuplicate($_POST['field'], $_POST['value'], $_POST['table']);
    echo json_encode(['duplicate' => $result]);
}

if(isset($_POST['type']) && $_POST['type'] === 'render'){
    $data = [];
    $letter = 'h';
    foreach ($_POST['data'] as $entry){
        $data[++$letter] = $entry;
    }
    $result = (new Ajax())->ajaxRender($_POST['template'], $data);
    echo json_encode(['render' => $result]);
}

if(isset($_POST['type']) && $_POST['type'] === 'track'){
    (new Ajax())->trackVisits($_POST['article']);
}