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
        $uploader = new FileUpload(__DIR__ . '/../../assets/img/categoryIcons/', $categoryData['name'] . '.svg', ['svg'], 20000, $_FILES);
        $uploader = $this->prepareUpload($uploader);
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $this->userRepository->closeDB();
        $sameCategory = $this->categoryRepository->findOneBy('name', $categoryData['name']);
        if($sameCategory === null && $uploader !== false){
            $viewPath = "../../assets/img/categoryIcons/" . $uploader->getFileName();
            $uploader->upload();
            $category = new Category(0, $categoryData['name'], $categoryData['description'], new DateTime(), $user, new DateTime(), $user, $viewPath);
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
    private function prepareUpload(FileUpload $uploader): FileUpload|false
    {
        $uploader->setFile($uploader->getFileName());
        if($uploader->checkFileSize() && !$uploader->checkIfFileExists() && $uploader->checkIfCorrectFileType()){
            return $uploader;
        }
        else{
            return false;
        }
    }
}