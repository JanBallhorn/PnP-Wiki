<?php

namespace App\Controller;

use App\Collection\UserCollection;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Exception;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Validator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

abstract class Controller
{
    protected string $url = "https://wiki.verplant-durch-aventurien.de";

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    protected function render(string $view, array $params = []): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $twig = new Environment($loader, ['debug' => true]);
        $twig->addExtension(new DebugExtension());
        $function = new TwigFunction('encodeImg', [$this, 'encodeImg']);
        $twig->addFunction($function);
        $function = new TwigFunction('replaceSpoiler', function($string){
            $string = str_replace("<span class='spoiler'>", "||", $string);
            return str_replace("</span>", "||", $string);
        });
        $twig->addFunction($function);
        $function = new TwigFunction('getContentHeadlines', function($string){
            $headlines = array();
            preg_match_all('|<h[3-4]\\sid="headline[0-9-]*">.*</h[3-4]>|', $string, $matches);
            $i = -1;
            foreach($matches[0] as $match){
                preg_match_all('|<h[3-4]\\sid="(headline[0-9-]*)">.*</h[3-4]>|', $match, $idMatches);
                preg_match_all('|<h[3-4]\\sid="headline[0-9-]*">(.*)</h[3-4]>|', $match, $headlineMatches);
                if($match[2] === "3"){
                    $headlines[] = ["id" => $idMatches[1][0], "headline" => $headlineMatches[1][0], "h4s" => []];
                    $i++;
                }
                elseif($match[2] === "4"){
                    $headlines[$i]["h4s"][] = ["id" => $idMatches[1][0], "headline" => $headlineMatches[1][0]];
                }
            }
            return $headlines;
        });
        $twig->addFunction($function);
        $function = new TwigFunction('getNonPrivate', [$this, 'getNonPrivate']);
        $twig->addFunction($function);
        $twigParams = [];
        $twigParams["loggedIn"] = $this->checkLogin();
        $twigParams["baseCategories"] = (new CategoryRepository())->findPopularCategories();
        $twigParams["users"] = $this->getAllUsersExceptCurrent();
        if($twigParams["loggedIn"] === true) {
            $user = (new UserRepository())->findOneBy("username", $this->getUsernameFromToken($this->getCookie()));
            $twigParams["wikiUser"] = $user;
            $twigParams["baseProjects"] = (new ProjectRepository())->findAllBetween(0, 5, $user->getId(), "searched DESC");
            $twigParams["popularArticles"] = (new ArticleRepository())->findAllBetween(0, 5, $user->getId(), "called DESC");
        }
        else{
            $twigParams["baseProjects"] = (new ProjectRepository())->findAllBetween(0, 5, 0, "searched DESC");
            $twigParams["popularArticles"] = (new ArticleRepository())->findAllBetween(0, 5, 0, "called DESC");
        }
        foreach ($params as $key => $value) {
            $twigParams[$key] = $value;
        }
        echo $twig->render($view, $twigParams);
    }
    protected function checkLogin(): bool
    {
        return isset($_COOKIE['login']);
    }
    protected function buildToken(string $username, int $userId, bool $remember): string
    {
        $tokenBuilder = new Builder(new JoseEncoder(), ChainedFormatter::default());
        $algorithm = new Sha256();
        $signingKey = InMemory::plainText("8VwxeMAGDkoz1nSuWD1tESkCOGgPk5Il");
        $now = new \DateTimeImmutable();
        if($remember === true){
            $expires = $now->add(\DateInterval::createFromDateString('6 months'));
        }
        else{
            $expires = $now->add(\DateInterval::createFromDateString('1 day'));
        }
        $token = $tokenBuilder
            ->issuedBy($this->url)
            ->relatedTo($username)
            ->identifiedBy($userId)
            ->issuedAt($now)
            ->expiresAt($expires)
            ->withClaim('username', $username)
            ->getToken($algorithm, $signingKey);
        return $token->toString();
    }
    protected function validateToken(string $token, string $username): bool
    {
        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($token);
        $validator = new Validator();
        return $validator->validate($token, new RelatedTo($username));
    }
    protected function getUsernameFromToken(?string $token): string
    {
        if($token === null){
            return "";
        }
        else{
            $parser = new Parser(new JoseEncoder());
            $token = $parser->parse($token);
            return $token->claims()->get('username');
        }
    }

    protected function createCookie(string $token, bool $remember): void
    {
        if($remember === true){
            $extratime = 15768000;
        }
        else{
            $extratime = 86400;
        }
        setcookie("login", $token, time() + $extratime, "/", "wiki.verplant-durch-aventurien.de", true, true);
    }
    protected function getCookie(): ?string
    {
        return $_COOKIE["login"] ?? null;
    }
    protected function destroyCookie(): void
    {
        if(isset($_COOKIE["login"])){
            setcookie("login", "", time() - 3600, "/", "wiki.verplant-durch-aventurien.de", true);
        }
    }
    public function encodeImg(string $img): string
    {
        $location = dirname($_SERVER['DOCUMENT_ROOT']);
        $image = $location . "/externalImages/" . $img;
        if(!exif_imagetype($image)){
            $imgType = "image/svg+xml";
        }
        else{
            $imgType = image_type_to_mime_type(exif_imagetype($image));
        }
        return "data:" . $imgType . ";base64," . base64_encode(file_get_contents($image));
    }

    /**
     * @throws Exception
     */
    public function getNonPrivate($collection){
        if($this->checkLogin()){
            $username = $this->getUsernameFromToken($this->getCookie());
            $user = (new UserRepository())->findOneBy('username', $username);
            $collection->rewind();
            $colCount = $collection->count();
            for($i = 0; $i < $colCount; $i++){
                $cur = $collection->current();
                if($cur->getPrivate() === true){
                    $authorized = $cur->getAuthorized();
                    $authorized->rewind();
                    $authed = false;
                    for($j = 0; $j < $authorized->count(); $j++){
                        if($user->getId() === $authorized->current()->getId()){
                            $authed = true;
                        }
                        $authorized->next();
                    }
                    if(!$authed){
                        $collection->offsetUnset($collection->key());
                    }
                }
                $collection->next();
            }
        }
        return $collection;
    }

    /**
     * @throws Exception
     */
    protected function getAllUsersExceptCurrent(): UserCollection
    {
        $username = $this->getUsernameFromToken($this->getCookie());
        $userRepository = new UserRepository();
        $user = $userRepository->findOneBy('username', $username);
        $users = $userRepository->findAll('username');
        if($user !== null){
            $users->rewind();
            for($i = 0; $i < $users->count(); $i++){
                if($users->current()->getId() === $user->getId()){
                    $users->offsetUnset($users->key());
                }
                $users->next();
            }
        }
        return $users;
    }
}