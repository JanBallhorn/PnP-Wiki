<?php

namespace App\Controller;

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
     */
    protected function render(string $view, array $params = []): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $twig = new Environment($loader, ['debug' => true]);
        $twig->addExtension(new DebugExtension());
        $function = new TwigFunction('encodeImg', [$this, 'encodeImg']);
        $twig->addFunction($function);
        $twigParams = [];
        $twigParams["loggedIn"] = $this->checkLogin();
        if($twigParams["loggedIn"] === true) {
            $twigParams["user"] = http_build_query(['user' => $this->getUsernameFromToken($this->getCookie())]);
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
    protected function getUsernameFromToken(string $token): string
    {
        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($token);
        return $token->claims()->get('username');
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
}