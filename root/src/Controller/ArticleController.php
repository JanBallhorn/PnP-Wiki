<?php

namespace App\Controller;

use App\Collection\ArticleCollection;
use App\Collection\ArticleInfoContentCollection;
use App\Collection\ArticleInfoGalleryCollection;
use App\Collection\CategoryCollection;
use App\Collection\ProjectCollection;
use App\FileUpload;
use App\Model\Article;
use App\Model\ArticleInfo;
use App\Model\ArticleInfoContent;
use App\Model\ArticleInfoGallery;
use App\Model\ArticleSource;
use App\Model\Paragraph;
use App\Model\ParagraphContent;
use App\Model\ParagraphGallery;
use App\Model\Source;
use App\Repository\ArticleInfoRepository;
use App\Repository\ArticleRepository;
use App\Repository\ArticleSourceRepository;
use App\Repository\CategoryRepository;
use App\Repository\ParagraphContentRepository;
use App\Repository\ParagraphGalleryRepository;
use App\Repository\ParagraphRepository;
use App\Repository\ProjectRepository;
use App\Repository\SourceRepository;
use App\Repository\UserRepository;
use DateTime;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleRepository $articleRepository = new ArticleRepository(),
        private readonly CategoryRepository $categoryRepository = new CategoryRepository(),
        private readonly ProjectRepository $projectRepository = new ProjectRepository(),
        private readonly UserRepository $userRepository = new UserRepository(),
        private readonly ParagraphRepository $paragraphRepository = new ParagraphRepository(),
        private readonly ParagraphContentRepository $paragraphContentRepository = new ParagraphContentRepository(),
        private readonly ParagraphGalleryRepository $paragraphGalleryRepository = new ParagraphGalleryRepository(),
        private readonly ArticleInfoRepository $articleInfoRepository = new ArticleInfoRepository(),
        private readonly SourceRepository $sourceRepository = new SourceRepository(),
        private readonly ArticleSourceRepository $articleSourceRepository = new ArticleSourceRepository(),
    ){}

    /**
     * @throws Exception
     */
    public function index(array $article): void
    {
        $article = $this->articleRepository->findOneBy('headline', $article['name']);
        $this->render('article.twig', ['article' => $article]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function create(): void
    {
        $projects = $this->getNonPrivate($this->projectRepository->findAll('name'));
        $categories = $this->categoryRepository->findAll('name');
        $this->render('createArticle.twig', ['projects' => $projects->__serialize(), 'categories' => $categories->__serialize()]);
    }

    /**
     * @throws Exception
     */
    public function save(array $article): void
    {
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $sameHeadline = $this->articleRepository->findOneBy('headline', $article['headline']);
        $altHeadlines = explode(",", $article['altHeadlines']);
        if($sameHeadline === null){
            $articles = $this->articleRepository->findAll();
            $allOtherAltHeadlines = [];
            foreach ($articles as $Article) {
                $otherAltHeadlines = $Article->getAltHeadlines();
                if($otherAltHeadlines !== null){
                    foreach ($otherAltHeadlines as $altHeadline) {
                        $allOtherAltHeadlines[] = $altHeadline;
                    }
                }
            }
            foreach ($altHeadlines as $altHeadline) {
                $sameHeadline = $this->articleRepository->findOneBy('headline', $altHeadline);
                if($sameHeadline !== null){
                    break;
                }
                if($allOtherAltHeadlines !== null){
                    if(in_array($altHeadline, $allOtherAltHeadlines)){
                        $sameHeadline = true;
                        break;
                    }
                }
                $allOtherAltHeadlines[] = $altHeadline;
            }
        }
        if($sameHeadline === null && !empty($article['project']) && isset($article['category'])){
            $categories = new CategoryCollection();
            foreach ($article['category'] as $category){
                $category = $this->categoryRepository->findById(intval($category));
                $categories->offsetSet($categories->key(), $category);
                $categories->next();
            }
            $categories->rewind();
            $project = $this->projectRepository->findOneBy('name', $article['project']);
            $tags = explode(",", $article['tags']);
            $private = false;
            if($project->getPrivate() || isset($article['private'])){
                $private = true;
            }
            $article = new Article(0, new DateTime(), $user, new DateTime(), $user, $article['headline'], $project, $categories, $tags, $altHeadlines, $private, isset($article['editable']), 0);
            $this->articleRepository->save($article);
            header("Location: /article?" . http_build_query(['name' => $article->getHeadline()]));
        }
        else{
            $projects = $this->projectRepository->findAll('name');
            $categories = $this->categoryRepository->findAll('name');
            $this->render('createArticle.twig', [
                'articleError' => true,
                'projects' => $projects->__serialize(),
                'categories' => $categories->__serialize(),
                'headline' => $article['headline'],
                'project' => $article['project'],
                'categoryIds' => $article['category'],
                'categoryError' => !isset($article['category']),
                'altHeadlines' => $article['altHeadlines'],
                'tags' => $article['tags'],
                'private' => isset($article['private']),
                'editable' => isset($article['editable'])
            ]);
        }
    }

    /**
     * @throws Exception
     */
    public function edit(array $article): void
    {
        $article = $this->articleRepository->findOneBy('headline', $article['name']);
        $projects = $this->getNonPrivate($this->projectRepository->findAll('name'));
        $this->render('editArticle.twig', [
            'article' => $article,
            'projects' => $projects->__serialize(),
            'categories' => $this->categoryRepository->findAll('name')->__serialize()
        ]);
    }

    /**
     * @throws Exception
     */
    public function update(array $articleData): void
    {
        $article = $this->articleRepository->findById($articleData['id']);
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $sameHeadline = $this->articleRepository->findOneBy('headline', $articleData['headline']);
        $sameAltHeadline = false;
        $altHeadlines = explode(",", $articleData['altHeadlines']);
        if($sameHeadline !== null){
            $sameHeadline = $sameHeadline->getId() !== $article->getId();
        }
        else{
            $sameHeadline = false;
        }
        if($sameHeadline === false){
            $articles = $this->articleRepository->findAll();
            $allOtherAltHeadlines = [];
            foreach ($articles as $Article) {
                if($Article->getId() !== $article->getId()){
                    $otherAltHeadlines = $Article->getAltHeadlines();
                    if($otherAltHeadlines !== null){
                        foreach ($otherAltHeadlines as $altHeadline) {
                            $allOtherAltHeadlines[] = $altHeadline;
                        }
                    }
                }
            }
            foreach ($altHeadlines as $altHeadline) {
                $sameAltHeadline = $this->articleRepository->findOneBy('headline', $altHeadline);
                if($sameAltHeadline !== null){
                    if($sameAltHeadline->getId() === $article->getId() && $article->getHeadline() === $articleData['headline']){
                        $sameAltHeadline = true;
                        break;
                    }
                    elseif ($sameAltHeadline->getId() !== $article->getId()){
                        $sameAltHeadline = true;
                        break;
                    }
                }
                if($allOtherAltHeadlines !== null){
                    if(in_array($altHeadline, $allOtherAltHeadlines)){
                        $sameAltHeadline = true;
                        break;
                    }
                }
                $allOtherAltHeadlines[] = $altHeadline;
            }
        }
        if($sameHeadline === false && $sameAltHeadline !== true && !empty($articleData['project']) && isset($articleData['category'])){
            $categories = new CategoryCollection();
            foreach ($articleData['category'] as $category){
                $category = $this->categoryRepository->findById(intval($category));
                $categories->offsetSet($categories->key(), $category);
                $categories->next();
            }
            $categories->rewind();
            $project = $this->projectRepository->findOneBy('name', $articleData['project']);
            $tags = explode(",", $articleData['tags']);
            $private = false;
            if($project->getPrivate() || isset($articleData['private'])){
                $private = true;
            }
            $article->setLastEdit(new DateTime());
            $article->setLastEditBy($user);
            $article->setHeadline($articleData['headline']);
            $article->setProject($project);
            $article->setCategories($categories);
            $article->setTags($tags);
            $article->setAltHeadlines($altHeadlines);
            $article->setPrivate($private);
            $article->setEditable(isset($articleData['editable']));
            $this->articleRepository->save($article);
            header("Location: /article?" . http_build_query(['name' => $article->getHeadline()]));
        }
        else{
            $this->render('editArticle.twig', [
                'articleError' => true,
                'projects' => $this->projectRepository->findAll('name')->__serialize(),
                'categories' => $this->categoryRepository->findAll('name')->__serialize(),
                'article' => $article,
                'categoryError' => !isset($articleData['category'])
            ]);
        }
    }

    /**
     * @throws Exception
     */
    public function editParagraphs(array $article): void
    {
        $article = $this->articleRepository->findOneBy('headline', $article['name']);
        $sources = $this->sourceRepository->findAll('name');
        $this->render('articleEditParagraphs.twig', ['article' => $article, 'sources' => $sources]);
    }

    /**
     * @throws Exception
     */
    public function saveParagraphs(array $articleData): void
    {
        $articleImages = json_decode($articleData['images'], true);
        if(!file_exists(__DIR__ . '/../../../externalImages/articleImg/' . $articleData['name'])){
            mkdir(__DIR__ . '/../../../externalImages/articleImg/' . $articleData['name']);
        }
        else{
            $files = glob(__DIR__ . '/../../../externalImages/articleImg/' . $articleData['name'] . "/*");
            foreach($files as $file){
                if(is_file($file)){
                    unlink($file);
                }
            }
        }
        $uploads = [];
        foreach ($articleImages as $element => $paragraphImages){
            $i = 1;
            foreach ($paragraphImages as $number => $image){
                $imgData = base64_decode(str_replace(' ', '+', substr($image, strpos($image, ",")+1)));
                $fileExtension = explode('/', mime_content_type($image))[1];
                $fileSize = strlen($imgData);
                $uploader = new FileUpload(__DIR__ . '/../../../externalImages/articleImg/' . $articleData['name'] . '/', str_replace('/', '-', $element . '-' . $i) . '.' . $fileExtension, ['svg', 'jpeg', 'png', 'webp', 'gif'], 1000000, []);
                $uploader->setFileSize($fileSize);
                $uploader->setTmpFile($imgData);
                $upload = $this->prepareUpload($uploader);
                if($upload !== false){
                    $uploads[] = $upload;
                }
                $i++;
            }
        }
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        foreach ($uploads as $upload){
            $file = fopen($upload->getFile(), 'w');
            fwrite($file, $upload->getTmpFile());
            fclose($file);
        }
        $article = $this->articleRepository->findOneBy('headline', $articleData['name']);
        $oldParagraphs = $this->paragraphRepository->findBy('article', $article->getId(), 'sequence');
        if($oldParagraphs !== null){
            $oldIntroduction = $oldParagraphs->offsetGet(0);
            $introduction = new Paragraph($oldIntroduction->getId(), $oldIntroduction->getPublished(), $oldIntroduction->getCreatedBy(), new DateTime(), $user, $article, "", 1);
            $this->paragraphRepository->save($introduction);
            $contents = $this->paragraphContentRepository->findBy('paragraph', $introduction->getId());
            foreach ($contents as $content){
                $this->paragraphContentRepository->delete($content);
            }
            $oldParagraphs->offsetUnset(0);
            $i = 1;
            if(count($articleData['headline']) > $oldParagraphs->count()){
                foreach ($articleData['headline'] as $headline){
                    if($oldParagraphs->count() >= $i){
                        $oldParagraph = $oldParagraphs->offsetGet($i);
                        $paragraph = new Paragraph($oldParagraph->getId(), $oldParagraph->getPublished(), $oldParagraph->getCreatedBy(), new DateTime(), $user, $article, $headline, $i + 1);
                        $contents = $this->paragraphContentRepository->findBy('paragraph', $oldParagraph->getId());
                        foreach ($contents as $content){
                            $this->paragraphContentRepository->delete($content);
                        }
                    }
                    else{
                        $paragraph = new Paragraph(0, new DateTime(), $user, new DateTime(), $user, $article, $headline, $i + 1);

                    }
                    $this->paragraphRepository->save($paragraph);
                    $i++;
                }
            }
            else{
                foreach($oldParagraphs->__serialize() as $oldParagraph){
                    if(count($articleData['headline']) >= $i){
                        $headline = $articleData['headline'][$i - 1];
                        $paragraph = new Paragraph($oldParagraph->getId(), $oldParagraph->getPublished(), $oldParagraph->getCreatedBy(), new DateTime(), $user, $article, $headline, $i + 1);
                        $this->paragraphRepository->save($paragraph);
                        $contents = $this->paragraphContentRepository->findBy('paragraph', $oldParagraph->getId());
                        if($contents !== null){
                            foreach ($contents as $content){
                            $this->paragraphContentRepository->delete($content);
                            }
                        }
                    }
                    else{
                        $this->paragraphRepository->delete($oldParagraph);
                    }
                    $i++;
                }
            }
        }
        else{
            $introduction = new Paragraph(0, new DateTime(), $user, new DateTime(), $user, $article, "", 1);
            $this->paragraphRepository->save($introduction);
            $i = 2;
            foreach ($articleData['headline'] as $headline){
                $paragraph = new Paragraph(0, new DateTime(), $user, new DateTime(), $user, $article, $headline, $i);
                $this->paragraphRepository->save($paragraph);
                $i++;
            }
        }
        foreach ($articleData as $element => $value){
            if(str_contains($element, 'text') || str_contains($element, 'gallery')){
                $paragraphs = $this->paragraphRepository->findBy('article', $article->getId(), 'sequence');
                preg_match_all('!\d+!', $element, $matches);
                $parNum = $matches[0][0] - 1;
                $paragraph = $paragraphs->offsetGet($parNum);
                $text = null;
                $img = null;
                $figcaption = null;
                $gallery = false;
                if(str_contains($element, 'text')){
                    $value = explode("||", $value);
                    $j = 0;
                    for ($i = 1; $i <= count($value); $i++){
                        $replace = "";
                        if($i === 4 * $j + 1 && $i !== count($value)){
                            $replace = "<span class='spoiler'>";
                        }
                        elseif($i === 4 * $j + 3){
                            $replace = "</span>";
                            $j++;
                        }
                        array_splice($value, $i, 0, $replace);
                        $i++;
                    }
                    $value = implode("", $value);
                    $value = preg_split('/<h3[A-Za-z0-9="\\s]*>/', $value);
                    for ($i = 0; $i < count($value); $i++){
                        if($i !== 0){
                            $curStr = preg_split('/<h4[A-Za-z0-9="\\s]*>/', $value[$i]);
                            for ($j = 0; $j < count($curStr); $j++){
                                if($j !== 0){
                                    $replace = '<h4 id="headline' . $parNum . "-" . round($i / 2) . "-" . round($j / 2) .'">';
                                    array_splice($curStr, $j, 0, $replace);
                                    $j++;
                                }
                            }
                            $value[$i] = implode("", $curStr);
                            $replace = '<h3 id="headline' . $parNum . "-" . round($i / 2) .'">';
                            array_splice($value, $i, 0, $replace);
                            $i++;
                        }
                    }
                    $value = implode("", $value);
                    $text = $value;
                    foreach ($uploads as $upload){
                        if(str_contains($upload->getFileName(), substr($element, 0, strpos($element,'text')))){
                            $img = 'articleImg/' . $articleData['name'] . '/' . $upload->getFileName();
                            $figcaption = $articleData[substr($element, 0, strpos($element,'text')) . 'figcaption'][0];
                            break;
                        }
                    }
                }
                else{
                    $gallery = true;
                }
                $paragraphContent = new ParagraphContent(0, $paragraph, $text, $img, $figcaption, $gallery, $element[3]);
                $this->paragraphContentRepository->save($paragraphContent);
                if(str_contains($element, 'gallery')){
                    $curEl = substr($element, 0, strpos($element,'gallery'));
                    foreach ($uploads as $upload){
                        if(str_contains($upload->getFileName(), $curEl)){
                            preg_match_all('!\d+!', $upload->getFileName(), $matches);
                            $imgOrder = $matches[0][2] - 1;
                            $figcaption = $articleData[$curEl . 'figcaption'][$imgOrder];
                            $contents = $this->paragraphContentRepository->findBy('paragraph', $paragraph->getId(), 'sequence');
                            preg_match_all('!\d+!', $curEl, $matches);
                            $conNum = $matches[0][1] - 1;
                            $content = $contents->offsetGet($conNum);
                            $paragraphGallery = new ParagraphGallery(0, $content, 'articleImg/' . $articleData['name'] . '/' . $upload->getFileName(), $figcaption, $imgOrder + 1);
                            $this->paragraphGalleryRepository->save($paragraphGallery);
                        }
                    }
                }
                $oldArticleSources = $this->articleSourceRepository->findBy('article', $article->getId());
                if($oldArticleSources !== null){
                    $oldArticleSources->rewind();
                    for ($i = 0; $i < $oldArticleSources->count(); $i++){
                        $this->articleSourceRepository->delete($oldArticleSources->current());
                        $oldArticleSources->next();
                    }
                }
                if(isset($articleData['source'])){
                    $articleSources = array();
                    $i = 0;
                    foreach ($articleData['source'] as $source){
                        $type = $articleData['sourceType'][$i];
                        $reference  = $articleData['reference'][$i];
                        if($source !== '' && $reference !== ''){
                            $articleSources[] = ['source' => $source, 'type' => $type, 'reference' => $reference];
                        }
                        $i++;
                    }
                    foreach ($articleSources as $source){
                        $sourceId = 0;
                        $sameSource = $this->sourceRepository->findOneBy('name', $source['source']);
                        if($sameSource === null){
                            $newSource = new Source($sourceId, $source['source'], $source['type']);
                            $this->sourceRepository->save($newSource);
                            $sameSource = $this->sourceRepository->findOneBy('name', $source['source']);
                        }
                        $newArticleSource = new ArticleSource(0, $article, $sameSource, $source['reference']);
                        $this->articleSourceRepository->save($newArticleSource);
                    }
                }
            }
        }
        header("Location: /article?" . http_build_query(['name' => $article->getHeadline()]));
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function editInfo(array $article): void
    {
        $article = $this->articleRepository->findOneBy('headline', $article['name']);
        $this->render('articleEditInfo.twig', ['article' => $article]);
    }

    /**
     * @throws Exception
     */
    public function saveInfo(array $info): void
    {
        $infoImages = json_decode($info['images'], true);
        if(!file_exists(__DIR__ . '/../../../externalImages/articleInfo/' . $info['name'])){
            mkdir(__DIR__ . '/../../../externalImages/articleInfo/' . $info['name']);
        }
        else{
            $files = glob(__DIR__ . '/../../../externalImages/articleInfo/' . $info['name'] . "/*");
            foreach($files as $file){
                if(is_file($file)){
                    unlink($file);
                }
            }
        }
        $uploads = [];
        $i = 1;
        foreach ($infoImages as $img){
            $imgData = base64_decode(str_replace(' ', '+', substr($img, strpos($img, ",")+1)));
            $fileExtension = explode('/', mime_content_type($img))[1];
            $fileSize = strlen($imgData);
            $uploader = new FileUpload(__DIR__ . '/../../../externalImages/articleInfo/' . $info['name'] . '/', str_replace('/', '-', $i) . '.' . $fileExtension, ['svg', 'jpeg', 'png', 'webp', 'gif'], 1000000, []);
            $uploader->setFileSize($fileSize);
            $uploader->setTmpFile($imgData);
            $upload = $this->prepareUpload($uploader);
            if($upload !== false){
                $uploads[] = $upload;
            }
            $i++;
        }
        foreach ($uploads as $upload){
            $file = fopen($upload->getFile(), 'w');
            fwrite($file, $upload->getTmpFile());
            fclose($file);
        }
        $article = $this->articleRepository->findOneBy('headline', $info['name']);
        $sameInfo = $this->articleInfoRepository->findOneBy('article', $article->getId());
        $infoContents = new ArticleInfoContentCollection();
        $infoGallery = new ArticleInfoGalleryCollection();
        foreach ($info['tableHeadline'] as $number => $headline){
            $topics = $info['rowTopic' . ($number + 1)];
            $infos = $info['rowInfo' . ($number + 1)];
            foreach ($topics as $topicNumber => $topic){
                if(!empty($topic) || !empty($infos[$topicNumber])){
                    $infoContent = new ArticleInfoContent(0, $topic, $infos[$topicNumber], $headline, $topicNumber + 1);
                    $infoContents->offsetSet($infoContents->key(), $infoContent);
                    $infoContents->next();
                }
            }
        }
        foreach ($uploads as $number => $upload){
            $infoImg = new ArticleInfoGallery(0, 'articleInfo/' . $info['name'] . '/' . $upload->getFileName(), $info['pcfigcaption'][$number], $number + 1);
            $infoGallery->offsetSet($infoGallery->key(), $infoImg);
            $infoGallery->next();
        }
        if($sameInfo !== null){
            $info = new ArticleInfo($sameInfo->getId(), $article, $info['mainHeadline'], $infoContents, $infoGallery);
        }
        else{
            $info = new ArticleInfo(0, $article, $info['mainHeadline'], $infoContents, $infoGallery);
        }
        $this->articleInfoRepository->save($info);
        header("Location: /article?" . http_build_query(['name' => $article->getHeadline()]));
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function list(array $filterData): void
    {
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $page = $filterData['page'];
        $filter = $filterData['filter'];
        if($filter === 'headline_down'){
            $filter = 'headline DESC';
        }
        elseif($filter === 'published_new'){
            $filter = 'published DESC';
        }
        elseif($filter === 'called'){
            $filter = 'called DESC';
        }
        if($user !== null){
            $articles = $this->articleRepository->findAllBetween(($page - 1) * 50 + 1, ($page - 1) * 50 + 50, $user->getId(), $filter);
            $articleNum = $this->articleRepository->getNumberOfArticles($user->getId());
            $pages = ceil($articleNum / 50);
        }
        else{
            $articles = null;
            $pages = null;
        }
        $this->render('articleList.twig', ['articles' => $articles, 'filter' => $filterData['filter'], 'page' => $page, 'pages' => $pages]);
    }

    /**
     * @throws Exception
     */
    public function random(): void
    {
        $articles = $this->getNonPrivate($this->articleRepository->findAll());
        $articles->rewind();
        $offsets = array();
        for($i = 0; $i < $articles->count(); $i++){
            if($articles->offsetExists($articles->key())){
                $offsets[] = $articles->key();
            }
            else{
                $i--;
            }
            $articles->next();
        }
        $randomNumber = $offsets[array_rand($offsets)];
        $article = $articles->offsetGet($randomNumber);
        header("Location: /article?" . http_build_query(['name' => $article->getHeadline()]));
    }

    private function prepareUpload(FileUpload $uploader): FileUpload|false
    {
        $uploader->setFile($uploader->getFileName());
        if($uploader->checkFileSize() && $uploader->checkIfCorrectFileType()){
            return $uploader;
        }
        else{
            return false;
        }
    }
}