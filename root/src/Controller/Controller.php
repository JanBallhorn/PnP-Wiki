<?php

namespace App\Controller;

use App\Collection\ArticleCollection;
use App\Collection\UserCollection;
use App\Env;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use DOMDocument;
use DOMElement;
use DOMNode;
use Exception;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

abstract class Controller
{
    protected function url(): string
    {
        return Env::getRequired('BASE_URL');
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    protected function render(string $view, array $params = []): void
    {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $twig = new Environment($loader, ['cache' => sys_get_temp_dir() . '/wiki_twig', 'auto_reload' => true]);
        $function = new TwigFunction('encodeImg', [$this, 'encodeImg']);
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
        // Adds a hover title (the target's current headline) to internal links
        // that don't have one yet - so links stored before the title feature
        // still reveal their target on hover, without a DB migration.
        $function = new TwigFunction('addLinkTitles', function($string){
            if(!is_string($string) || $string === '' || stripos($string, 'article?id=') === false){
                return $string;
            }
            if(!preg_match_all('/href\\s*=\\s*["\'][^"\']*article\\?id=(\\d+)/i', $string, $ids)){
                return $string;
            }
            $map = (new ArticleRepository())->headlinesByIds(array_map('intval', $ids[1]));
            if(count($map) === 0){
                return $string;
            }
            return preg_replace_callback('/<a\\b([^>]*)>/i', function($m) use ($map){
                $attrs = $m[1];
                if(preg_match('/\\btitle\\s*=/i', $attrs)){
                    return $m[0];
                }
                if(!preg_match('/\\bhref\\s*=\\s*["\'][^"\']*article\\?id=(\\d+)/i', $attrs, $h)){
                    return $m[0];
                }
                $id = (int)$h[1];
                if(!isset($map[$id])){
                    return $m[0];
                }
                return '<a' . $attrs . ' title="' . htmlspecialchars($map[$id], ENT_QUOTES) . '">';
            }, $string);
        });
        $twig->addFunction($function);
        // Rewrites links that point at our OWN domain(s) to relative paths, so an
        // internal link keeps you on whatever environment you're viewing (local
        // stays local, live stays live) instead of jumping to the hard-coded host
        // some old links carry. Links to other domains (Wikipedia etc.) are left
        // absolute and untouched. Own hosts = SITE_HOSTS plus the BASE_URL host.
        $function = new TwigFunction('relativizeInternalLinks', function($string){
            if(!is_string($string) || stripos($string, 'http') === false){
                return $string;
            }
            $ownHosts = array_filter(array_map('trim', explode(',', (string)(Env::get('SITE_HOSTS') ?? ''))));
            $baseHost = parse_url(Env::getRequired('BASE_URL'), PHP_URL_HOST);
            if(is_string($baseHost) && $baseHost !== ''){
                $ownHosts[] = $baseHost;
            }
            $ownHosts = array_values(array_unique(array_map('strtolower', $ownHosts)));
            if(count($ownHosts) === 0){
                return $string;
            }
            return preg_replace_callback('/\\bhref\\s*=\\s*(["\'])([^"\']*)\\1/i', function($m) use ($ownHosts){
                $quote = $m[1];
                $host = parse_url($m[2], PHP_URL_HOST);
                if(!is_string($host) || !in_array(strtolower($host), $ownHosts, true)){
                    return $m[0]; // relative already, or a foreign domain -> leave as is
                }
                $path = parse_url($m[2], PHP_URL_PATH);
                $query = parse_url($m[2], PHP_URL_QUERY);
                $fragment = parse_url($m[2], PHP_URL_FRAGMENT);
                $rel = (is_string($path) && $path !== '' ? $path : '/')
                    . (is_string($query) ? '?' . $query : '')
                    . (is_string($fragment) ? '#' . $fragment : '');
                return 'href=' . $quote . $rel . $quote;
            }, $string);
        });
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
    /**
     * Whitelists the `filter` request param into a fixed ORDER BY expression.
     * Never pass request input straight into a query's ORDER BY clause -
     * unlike WHERE values, it can't be parameterized via bind_param.
     */
    protected function resolveArticleOrder(string $filter): string
    {
        return match ($filter) {
            'headline' => 'headline',
            'headline_down' => 'headline DESC',
            'published' => 'published',
            'published_new' => 'published DESC',
            'called' => 'called DESC',
            default => 'articles.id',
        };
    }

    /**
     * Strips script-execution vectors (script/style/iframe/etc. tags, event-handler
     * attributes, javascript:/data: URLs) from user-submitted HTML before it's stored,
     * while preserving the formatting markup the paragraph/info editors produce
     * (bold, links, tables, headings, spoiler spans, alignment styles, ...).
     * Every value that later gets rendered with Twig's `|raw` filter must go through
     * this first - `|raw` performs no escaping of its own.
     */
    protected function sanitizeHtml(?string $html): ?string
    {
        if($html === null || trim($html) === ''){
            return $html;
        }
        $deniedTags = ['script', 'style', 'iframe', 'object', 'embed', 'applet', 'form', 'input',
            'button', 'textarea', 'select', 'option', 'link', 'meta', 'base', 'svg', 'math', 'noscript'];
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . '<div id="sanitize-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $doc->documentElement;
        if($root === null){
            return '';
        }
        $this->sanitizeHtmlNode($root, $deniedTags);
        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $doc->saveHTML($child);
        }
        return $result;
    }

    private function sanitizeHtmlNode(DOMNode $node, array $deniedTags): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if(!$child instanceof DOMElement){
                continue;
            }
            if(in_array(strtolower($child->tagName), $deniedTags, true)){
                $node->removeChild($child);
                continue;
            }
            foreach (iterator_to_array($child->attributes) as $attr) {
                $name = strtolower($attr->name);
                if(str_starts_with($name, 'on') || in_array($name, ['srcdoc', 'formaction'], true)){
                    $child->removeAttribute($attr->name);
                    continue;
                }
                if(in_array($name, ['href', 'src', 'action'], true)){
                    $normalized = strtolower(trim(preg_replace('/[\x00-\x1F\s]+/', '', $attr->value)));
                    if(preg_match('/^(javascript|vbscript|data):/', $normalized)){
                        $child->removeAttribute($attr->name);
                    }
                }
                if($name === 'style' && preg_match('/expression\s*\(|javascript:|vbscript:/i', $attr->value)){
                    $child->removeAttribute($attr->name);
                }
            }
            $this->sanitizeHtmlNode($child, $deniedTags);
        }
    }

    protected function checkLogin(): bool
    {
        return $this->getVerifiedToken($this->getCookie()) !== null;
    }
    private function signingKey(): InMemory
    {
        return InMemory::plainText(Env::getRequired('JWT_SECRET'));
    }
    /**
     * Parses the given token and verifies its signature and expiry. Returns
     * null if the token is missing, malformed, unsigned by us or expired,
     * so callers never trust an unverified token's claims.
     */
    private function getVerifiedToken(?string $token): ?UnencryptedToken
    {
        if($token === null){
            return null;
        }
        $parser = new Parser(new JoseEncoder());
        try{
            $parsedToken = $parser->parse($token);
        }
        catch(\Exception){
            return null;
        }
        if(!$parsedToken instanceof UnencryptedToken){
            return null;
        }
        $validator = new Validator();
        if(!$validator->validate($parsedToken, new SignedWith(new Sha256(), $this->signingKey()))){
            return null;
        }
        if($parsedToken->isExpired(new \DateTimeImmutable())){
            return null;
        }
        return $parsedToken;
    }
    protected function buildToken(string $username, int $userId, bool $remember): string
    {
        $tokenBuilder = new Builder(new JoseEncoder(), ChainedFormatter::default());
        $algorithm = new Sha256();
        $signingKey = $this->signingKey();
        $now = new \DateTimeImmutable();
        if($remember === true){
            $expires = $now->add(\DateInterval::createFromDateString('6 months'));
        }
        else{
            $expires = $now->add(\DateInterval::createFromDateString('1 day'));
        }
        $token = $tokenBuilder
            ->issuedBy($this->url())
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
        $verifiedToken = $this->getVerifiedToken($token);
        if($verifiedToken === null){
            return false;
        }
        $validator = new Validator();
        return $validator->validate($verifiedToken, new RelatedTo($username));
    }
    protected function getUsernameFromToken(?string $token): string
    {
        $verifiedToken = $this->getVerifiedToken($token);
        if($verifiedToken === null){
            return "";
        }
        else{
            return $verifiedToken->claims()->get('username');
        }
    }

    protected function createCookie(string $token, bool $remember): void
    {
        if($remember === true){
            $extratime = (int) Env::getRequired('COOKIE_LIFETIME_REMEMBER');
        }
        else{
            $extratime = (int) Env::getRequired('COOKIE_LIFETIME_DEFAULT');
        }
        setcookie("login", $token, [
            'expires' => time() + $extratime,
            'path' => '/',
            // empty domain => host-only cookie, needed on localhost where a
            // "Domain=localhost" attribute is rejected by browsers
            'domain' => Env::get('COOKIE_DOMAIN') ?? '',
            // secure by default; only disabled for local plain-HTTP development
            'secure' => (Env::get('COOKIE_SECURE') ?? 'true') !== 'false',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    protected function getCookie(): ?string
    {
        return $_COOKIE["login"] ?? null;
    }
    protected function destroyCookie(): void
    {
        if(isset($_COOKIE["login"])){
            $secure = (Env::get('COOKIE_SECURE') ?? 'true') !== 'false';
            setcookie("login", "", time() - 3600, "/", Env::get('COOKIE_DOMAIN') ?? '', $secure);
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
     * Replaces each article category's stored icon path with an inline
     * base64 data-URI so the icons show up in article overviews - the SVGs
     * live outside the web root and can't be linked directly (same reason
     * CategoryController::index() encodes them). Skips icons that are already
     * encoded: the repository hands out shared cached Category instances, so
     * an icon may already have been converted by an earlier call (e.g. the
     * home page encodes two article lists); re-encoding would try to read the
     * data-URI as a file path.
     */
    public function encodeArticleCategoryIcons(?ArticleCollection $articles): void
    {
        if($articles === null){
            return;
        }
        foreach($articles as $article){
            $categories = $article->getCategories();
            if($categories === null){
                continue;
            }
            foreach($categories as $category){
                $icon = $category->getIcon();
                if($icon === null || str_starts_with($icon, 'data:')){
                    continue;
                }
                $category->setIcon($this->encodeImg($icon));
            }
        }
        $articles->rewind();
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