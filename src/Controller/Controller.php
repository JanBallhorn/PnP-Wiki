<?php

namespace App\Controller;

use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Validator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
    protected string $url = "https://wiki.verplant-durch-aventurien.de";
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    function render(string $view, array $params = []): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $twig = new Environment($loader);
        $twigParams = [];
        foreach ($params as $key => $value) {
            $twigParams[$key] = $value;
        }
        echo $twig->render($view, $twigParams);
    }
    function buildToken(string $username, int $userId, bool $remember): string
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
            ->getToken($algorithm, $signingKey);
        return $token->toString();
    }
    function validateToken(string $token, string $username): bool
    {
        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($token);
        $validator = new Validator();
        if($validator->validate($token, new RelatedTo($username))){
            return true;
        }
        else{
            return false;
        }
    }

    function createCookie(string $token, bool $remember): void
    {
        if($remember === true){
            $extratime = 15768000;
        }
        else{
            $extratime = 86400;
        }
        setcookie("login", $token, time() + $extratime, "/", "wiki.verplant-durch-aventurien.de", true, true);
    }
    function getCookie(): ?string{
        if(isset($_COOKIE["login"])){
            return $_COOKIE["login"];
        }
        else{
            return null;
        }
    }
}