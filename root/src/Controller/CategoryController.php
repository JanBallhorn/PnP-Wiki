<?php

namespace App\Controller;

use App\Collection\CategoryInfoTemplateCollection;
use App\Collection\CategorySectionTemplateCollection;
use App\FileUpload;
use App\Model\Category;
use App\Model\CategoryInfoTemplate;
use App\Model\CategorySectionTemplate;
use App\Repository\ArticleRepository;
use App\Repository\CategoryInfoTemplateRepository;
use App\Repository\CategorySectionTemplateRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use DateMalformedStringException;
use DateTime;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CategoryController extends Controller
{
    private string $template = 'category.twig';

    public function __construct(
        private readonly CategoryRepository $categoryRepository = new CategoryRepository(),
        private readonly UserRepository $userRepository = new UserRepository(),
        private readonly ArticleRepository $articleRepository = new ArticleRepository(),
        private readonly CategoryInfoTemplateRepository $templateRepository = new CategoryInfoTemplateRepository(),
        private readonly CategorySectionTemplateRepository $sectionTemplateRepository = new CategorySectionTemplateRepository()
    ){}

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError|DateMalformedStringException
     */
    public function index(): void
    {
        $categories = $this->categoryRepository->findAll('name');
        foreach ($categories as $category) {
            if($category->getIcon() !== null){
                $category->setIcon($this->encodeImg($category->getIcon()));
                $categories->offsetSet($categories->key(), $category);
            }
        }
        $this->render($this->template, ['categories' => $categories]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function create(): void
    {
        $this->render('createCategory.twig');
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function save(array $categoryData): void
    {
        if(!$this->checkLogin()){
            header("Location: /category");
            return;
        }
        if(!empty($_FILES["fileUpload"]["tmp_name"])){
            $uploader = new FileUpload(__DIR__ . '/../../../externalImages/categoryIcons/', str_replace('/', '-', $categoryData['name']) . '.svg', ['svg'], 20000, $_FILES);
            $upload = $this->prepareUpload($uploader);
        }
        else{
            $uploader = null;
            $upload = null;
        }
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $sameCategory = $this->categoryRepository->findOneBy('name', $categoryData['name']);
        if($sameCategory === null && $upload !== false){
            if($upload !== null){
                $upload->upload();
                $icon = "categoryIcons/" . $upload->getFileName();
            }
            else{
                $icon = null;
            }
            $category = new Category(0, $categoryData['name'], $categoryData['description'], new DateTime(), $user, new DateTime(), $user, $icon);
            $this->categoryRepository->save($category);
            $newCategory = $this->categoryRepository->findOneBy('name', $categoryData['name']);
            $this->saveTemplate($newCategory->getId(), $categoryData);
            $this->saveSectionTemplate($newCategory->getId(), $categoryData);
            header("Location: /category");
        }
        else{
            $errorFileSize = !$uploader?->checkFileSize();
            $errorFileType = !$uploader?->checkIfCorrectFileType();
            $this->render('createCategory.twig', [
                'categoryError' => true,
                'name' => $categoryData['name'],
                'desc' => $categoryData['description'],
                'errorFileSize' => $errorFileSize,
                'errorFileType' => $errorFileType
            ]);
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError|DateMalformedStringException
     * @throws Exception
     */
    public function detail(array $categoryData): void
    {
        $category = $this->categoryRepository->findById($categoryData['id']);
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $page = $categoryData['page'];
        $filter = $this->resolveArticleOrder($categoryData['filter']);
        if($user !== null){
            $articles = $this->articleRepository->findAllBetween(($page - 1) * 50, 50, $user->getId(), $filter, $category);
            $articleNum = $this->articleRepository->getNumberOfArticles($user->getId(), $category);
            $pages = (int)ceil($articleNum / 50);
        }
        else{
            $articles = null;
            $pages = null;
        }
        $filter = $categoryData['filter'];
        $this->render('categoryDetail.twig', ['category' => $category, 'articles' => $articles, 'filter' => $filter, 'page' => $page, 'pages' => $pages]);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError|DateMalformedStringException
     */
    public function edit(array $category): void
    {
        $category = $this->categoryRepository->findById($category['id']);
        $template = $this->templateRepository->findBy('category', $category->getId(), 'sequence');
        $sectionTemplate = $this->sectionTemplateRepository->findBy('category', $category->getId(), 'sequence');
        $this->render('editCategory.twig', ['category' => $category, 'template' => $template, 'sectionTemplate' => $sectionTemplate]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError|DateMalformedStringException
     */
    public function update(array $categoryData): void
    {
        if(!$this->checkLogin()){
            header("Location: /category");
            return;
        }
        $category = $this->categoryRepository->findById($categoryData['id']);
        $sameCategory = $this->categoryRepository->findOneBy('name', $categoryData['name']);
        $category->setName($categoryData['name']);
        $category->setDescription($categoryData['description']);
        if(!empty($_FILES['fileUpload']['name'])){
            $uploader = new FileUpload(__DIR__ . '/../../../externalImages/categoryIcons/', str_replace('/', '-', $categoryData['name']) . '.svg', ['svg'], 20000, $_FILES);
            $upload = $this->prepareUpload($uploader);
        }
        if(($sameCategory === null || $sameCategory->getId() === $category->getId()) && isset($upload) && $upload !== false){
            $upload->upload();
            $icon = "categoryIcons/" . $upload->getFileName();
            $category->setIcon($icon);
            $this->categoryRepository->save($category);
            $this->saveTemplate($category->getId(), $categoryData);
            $this->saveSectionTemplate($category->getId(), $categoryData);
            header("Location: /category");
        }
        elseif(($sameCategory === null || $sameCategory->getId() === $category->getId()) && !isset($upload)){
            $this->categoryRepository->save($category);
            $this->saveTemplate($category->getId(), $categoryData);
            $this->saveSectionTemplate($category->getId(), $categoryData);
            header("Location: /category");
        }
        else{
            if(isset($uploader) && isset($upload) && $upload === false){
                $errorFileSize = !$uploader->checkFileSize();
                $errorFileType = !$uploader->checkIfCorrectFileType();
            }
            else{
                $errorFileSize = false;
                $errorFileType = false;
            }
            $this->render('editCategory.twig', [
                'categoryError' => true,
                'name' => $categoryData['name'],
                'desc' => $categoryData['description'],
                'errorFileSize' => $errorFileSize,
                'errorFileType' => $errorFileType
            ]);
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    public function delete(array $category): void
    {
        if(!$this->checkLogin()){
            header("Location: /category");
            return;
        }
        $category = $this->categoryRepository->findById($category['id']);
        $this->categoryRepository->delete($category);
        header("Location: /category");
    }
    /**
     * Parses the infobox template rows from the category form (parallel
     * templateGroup[]/templateTopic[] arrays) into template entities and
     * replaces the category's stored template. Rows without a field name are
     * dropped; sequence follows the submitted order.
     */
    private function saveTemplate(int $categoryId, array $data): void
    {
        $rows = new CategoryInfoTemplateCollection();
        $topics = $data['templateTopic'] ?? [];
        $groups = $data['templateGroup'] ?? [];
        if(is_array($topics)){
            $sequence = 1;
            foreach ($topics as $index => $topic){
                $topic = trim($topic);
                $group = trim($groups[$index] ?? '');
                if($topic === ''){
                    continue;
                }
                $row = new CategoryInfoTemplate(0, $categoryId, $this->sanitizeHtml($group), $this->sanitizeHtml($topic), $sequence);
                $rows->offsetSet($rows->key(), $row);
                $rows->next();
                $sequence++;
            }
        }
        $this->templateRepository->saveForCategory($categoryId, $rows);
    }

    /**
     * Parses the section template rows (sectionHeadline[]) from the category
     * form into template entities and replaces the category's stored section
     * template. Rows without a headline are dropped; sequence follows the
     * submitted order.
     */
    private function saveSectionTemplate(int $categoryId, array $data): void
    {
        $rows = new CategorySectionTemplateCollection();
        $headlines = $data['sectionHeadline'] ?? [];
        if(is_array($headlines)){
            $sequence = 1;
            foreach ($headlines as $headline){
                $headline = trim($headline);
                if($headline === ''){
                    continue;
                }
                $row = new CategorySectionTemplate(0, $categoryId, $this->sanitizeHtml($headline), $sequence);
                $rows->offsetSet($rows->key(), $row);
                $rows->next();
                $sequence++;
            }
        }
        $this->sectionTemplateRepository->saveForCategory($categoryId, $rows);
    }

    private function prepareUpload(FileUpload $uploader): FileUpload|false
    {
        $uploader->setFile($uploader->getFileName());
        if($uploader->checkFileSize() && $uploader->checkIfCorrectFileType() && $uploader->checkContentMatchesDeclaredType()){
            return $uploader;
        }
        else{
            return false;
        }
    }
}