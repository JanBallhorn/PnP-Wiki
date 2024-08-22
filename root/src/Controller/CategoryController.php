<?php

namespace App\Controller;

use App\Controller\Controller;
use App\FileUpload;
use App\Model\Category;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use DateTime;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CategoryController extends Controller
{
    private string $template = 'category.twig';
    public function __construct(private readonly CategoryRepository $categoryRepository = new CategoryRepository(), private readonly UserRepository $userRepository = new UserRepository()){

    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(): void
    {
        $categories = $this->categoryRepository->findAll('name');
        $this->categoryRepository->closeDB();
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
     */
    public function save(array $categoryData): void
    {
        $uploader = new FileUpload(__DIR__ . '/../../../externalImages/categoryIcons/', str_replace('/', '-', $categoryData['name']) . '.svg', ['svg'], 20000, $_FILES);
        $upload = $this->prepareUpload($uploader);
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $this->userRepository->closeDB();
        $sameCategory = $this->categoryRepository->findOneBy('name', $categoryData['name']);
        if($sameCategory === null && $upload !== false){
            $upload->upload();
            $icon = $this->encodeImg("categoryIcons/" . $upload->getFileName());
            $category = new Category(0, $categoryData['name'], $categoryData['description'], new DateTime(), $user, new DateTime(), $user, $icon);
            $this->categoryRepository->save($category);
            $this->categoryRepository->closeDB();
            header("Location: /category");
        }
        else{
            $errorFileSize = !$uploader->checkFileSize();
            $errorFileType = !$uploader->checkIfCorrectFileType();
            $this->categoryRepository->closeDB();
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
     * @throws LoaderError
     */
    public function detail(array $categoryName): void
    {
        $category = $this->categoryRepository->findOneBy('name', $categoryName['name']);
        $this->categoryRepository->closeDB();
        $this->render('categoryDetail.twig', ['category' => $category]);

    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function edit(array $categoryName): void
    {
        $category = $this->categoryRepository->findOneBy('name', $categoryName['name']);
        $this->categoryRepository->closeDB();
        $this->render('editCategory.twig', ['category' => $category]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function update(array $categoryData): void
    {
        $category = $this->categoryRepository->findById($categoryData['id']);
        $sameCategory = $this->categoryRepository->findOneBy('name', $categoryData['name']);
        $category->setName($categoryData['name']);
        $category->setDescription($categoryData['description']);
        if(!empty($_FILES['fileUpload']['name'])){
            $uploader = new FileUpload(__DIR__ . '/../../../externalImages/categoryIcons/', str_replace('/', '-', $categoryData['name']) . '.svg', ['svg'], 20000, $_FILES);
            $upload = $this->prepareUpload($uploader);
        }
        if($sameCategory->getId() === $category->getId() && isset($upload) && $upload !== false){
            $upload->upload();
            $icon = $this->encodeImg("categoryIcons/" . $upload->getFileName());
            $category->setIcon($icon);
            $this->categoryRepository->save($category);
            $this->categoryRepository->closeDB();
            header("Location: /category");
        }
        elseif($sameCategory->getId() === $category->getId() && !isset($upload)){
            $this->categoryRepository->save($category);
            $this->categoryRepository->closeDB();
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
            $this->categoryRepository->closeDB();
            $this->render('editCategory.twig', [
                'categoryError' => true,
                'name' => $categoryData['name'],
                'desc' => $categoryData['description'],
                'errorFileSize' => $errorFileSize,
                'errorFileType' => $errorFileType
            ]);
        }
    }
    public function delete(array $categoryName): void
    {
        $category = $this->categoryRepository->findOneBy('name', $categoryName['name']);
        $this->categoryRepository->delete($category);
        $this->categoryRepository->closeDB();
        header("Location: /category");
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