<?php

namespace App;
use App\Collection\SourceCollection;
use App\Repository\ArticleRepository;
use App\Repository\ProjectRepository;
use App\Repository\SourceRepository;
use Exception;
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

    public function __construct()
    {
        $this->db = Database::dbConnect()->getConnection();
    }
    protected function closeDB(): void
    {
        $this->db->close();
    }

    function checkDuplicate(string $field, string $value, string $table): bool
    {
        $allowedFieldsByTable = [
            'categories' => ['name'],
            'projects' => ['name'],
            'articles' => ['headline'],
            'article_alt_headline' => ['headline'],
            'users' => ['email', 'username'],
        ];
        if(!isset($allowedFieldsByTable[$table]) || !in_array($field, $allowedFieldsByTable[$table], true)){
            return false;
        }
        $query = "SELECT COUNT(`$field`) FROM `$table` WHERE `$field` = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
        return (bool)$result[0];
    }

    /**
     * Only templates inside Views/fragments are reachable here - that
     * directory holds nothing but the small partials this endpoint is
     * meant to render, so a template name coming straight from the
     * request can't reach the rest of the application's templates.
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    function ajaxRender($template, $data): string
    {
        $loader = new FilesystemLoader(__DIR__ . '/Views/fragments');
        $twig = new Environment($loader, ['debug' => true]);
        return $twig->render($template, $data);
    }

    function trackVisits(string $article): void
    {
        $query = "UPDATE `articles` SET `called` = `called` + 1 WHERE `headline` = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $article);
        $stmt->execute();
    }

    function getSources(): ?SourceCollection
    {
        return (new SourceRepository())->findAll('name');
    }

    /**
     * @throws Exception
     */
    function getPrivateAndAuth($projectName): ?array
    {
        $project = (new ProjectRepository())->findOneBy("name", $projectName);
        if($project !== null){
            return ["private" => $project->getPrivate(), "auth" => $project->getAuthorized()];
        }
        else{
            return null;
        }
    }
}


if(isset($_POST['errorType']) && $_POST['errorType'] === 'duplicate'){
    $result = (new Ajax())->checkDuplicate($_POST['field'], $_POST['value'], $_POST['table']);
    echo json_encode(['duplicate' => $result]);
}

if(isset($_POST['errorType']) && $_POST['errorType'] === 'altHeadlineDuplicate'){
    $result = array();
    $origAlts = [];
    foreach ($_POST['value'] as $value) {
        $result[trim($value)] = array();
        $result[trim($value)][] = (new Ajax())->checkDuplicate('headline', trim($value), 'article_alt_headline');
        $result[trim($value)][] = (new Ajax())->checkDuplicate('headline', trim($value), 'articles');
    }
    if(!empty($_POST['orig'])){
        try {
            $article = (new ArticleRepository())->findOneBy('headline', $_POST['orig']);
            $origAlts = $article->getAltHeadlines();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
    echo json_encode(['duplicate' => $result, 'origAlts' => $origAlts]);
}

if(isset($_POST['type']) && $_POST['type'] === 'render' && preg_match('/^[A-Za-z0-9_-]+\.twig$/', $_POST['template'])){
    $ajax = new Ajax();
    if($_POST['template'] === 'newSource.twig'){
        $data['sources'] = $ajax->getSources()->__serialize();
    }
    else{
        $data = [];
        $letter = 'h';
        foreach ($_POST['data'] as $entry){
            $data[++$letter] = $entry;
        }
    }
    $result = $ajax->ajaxRender($_POST['template'], $data);
    echo json_encode(['render' => $result]);
}

if(isset($_POST['type']) && $_POST['type'] === 'track'){
    (new Ajax())->trackVisits($_POST['article']);
}

if(isset($_POST['type']) && $_POST['type'] === 'privateAndAuth'){
    $result = (new Ajax())->getPrivateAndAuth($_POST['project']);
    if($result !== null){
        $users = $result['auth'];
        if($users !== null){
            $users->rewind();
            $userIds = array();
            for ($i = 0; $i < $users->count(); $i++){
                $userIds[] = $users->current()->getId();
                $users->next();
            }
        }
        else{
            $userIds = null;
        }
        echo json_encode(['private' => $result['private'], 'auth' => $userIds]);
    }
    else{
        echo json_encode(null);
    }
}