<?php

namespace App\Controller;

use App\Model\Project;
use App\Repository\ArticleRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use DateTime;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ProjectController extends Controller
{
    private string $template = 'project.twig';
    public function __construct(
        private readonly ProjectRepository $projectRepository = new ProjectRepository(),
        private readonly UserRepository $userRepository = new UserRepository(),
        private readonly ArticleRepository $articleRepository = new ArticleRepository()
    )
    {

    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function index(): void
    {
        $projects = $this->getNonPrivate($this->projectRepository->findBy('parent_project', null, 'name'));
        $this->render($this->template, ['mainProjects' => $projects->__serialize()]);
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
        $this->render('createProject.twig', ['projects' => $projects->__serialize()]);
    }

    /**
     * @throws Exception
     */
    public function save(array $projectData): void
    {
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $parentProject = $this->projectRepository->findOneBy('name', $projectData['parentProject']);
        $sameProject = $this->projectRepository->findOneBy('name', $projectData['name']);
        $private = false;
        if(($parentProject !== null && $parentProject->getPrivate()) || isset($projectData['private'])){
            $private = true;
        }
        if($sameProject === null && ($parentProject !== null || $projectData['parentProject'] === '')) {
            $project = new Project(0, $projectData['name'], $projectData['description'], new DateTime(), $user, new DateTime(), $user, $parentProject, $private, 0);
            $this->projectRepository->save($project);
            header("Location: /project");
        }
        else{
            $projects = $this->projectRepository->findAll();
            $this->render('createProject.twig', [
                'projects' => $projects->__serialize(),
                'projectError' => true,
                'name' => $projectData['name'],
                'desc' => $projectData['description'],
                'parent' => $projectData['parentProject'],
                'private' => isset($projectData['private'])
            ]);
        }
    }

    /**
     * @throws Exception
     */
    public function detail(array $project): void
    {
        $error = false;
        if(isset($project['error'])){
            $error = $project['error'];
        }
        $project = $this->projectRepository->findOneBy('name', $project['name']);
        $this->render("projectDetail.twig", ['project' => $project, 'deleteError' => $error]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function edit(array $project): void
    {
        $projects = $this->getNonPrivate($this->projectRepository->findAll());
        $project = $this->projectRepository->findOneBy('name', $project['name']);
        $this->render("editProject.twig", ['projects' => $projects->__serialize(), 'project' => $project]);
    }

    /**
     * @throws Exception
     */
    public function update(array $projectData): void
    {
        $project = $this->projectRepository->findById($projectData['id']);
        $parentProject = $this->projectRepository->findOneBy('name', $projectData['parentProject']);
        $sameProject = $this->projectRepository->findOneBy('name', $projectData['name']);
        $oldPrivateState = $project->getPrivate();
        $private = false;
        if(($parentProject !== null && $parentProject->getPrivate()) || (isset($projectData['private']) && ($projectData['private'] === true || $projectData['private'] === "private"))){
            $private = true;
        }
        $project->setName($projectData['name']);
        $project->setDescription($projectData['description']);
        $project->setParentProject($parentProject);
        $project->setPrivate($private);
        if($sameProject->getId() === $project->getId() && ($parentProject !== null || $projectData['parentProject'] === '')) {
            $this->projectRepository->save($project);
            if($oldPrivateState !== $private){
                $articles = $this->articleRepository->findBy('project', $project->getId());
                if($articles !== null){
                    $articles->rewind();
                    for($i = 0; $i < $articles->count(); $i++){
                        $article = $articles->current();
                        $article->setPrivate($private);
                        $this->articleRepository->save($article);
                        $articles->next();
                    }
                }
                $childProjects = $project->getChildren();
                if($childProjects !== null){
                    $childProjects->rewind();
                    for($i = 0; $i < $childProjects->count(); $i++){
                        $childProject = $childProjects->current();
                        $this->update([
                            'id' => $childProject->getId(),
                            'name' => $childProject->getName(),
                            'description' => $childProject->getDescription(),
                            'parentProject' => $childProject->getParentProject()->getName(),
                            'private' => $private
                        ]);
                        $childProjects->next();
                    }
                }
            }
            header("Location: /project/detail?" . http_build_query(['name'=>$project->getName()]));
        }
        else{
            $projects = $this->projectRepository->findAll();
            $this->render("editProject.twig", ['projects' => $projects->__serialize(), 'project' => $project]);
        }
    }

    /**
     * @throws Exception
     */
    public function delete(array $projectName): void
    {
        $project = $this->projectRepository->findOneBy('name', $projectName['name']);
        if($project->getChildren() === null){
            $this->projectRepository->delete($project);
            header("Location: /project");
        }
        else{
            header("Location: /project/detail?" . http_build_query(['name'=>$project->getName(), 'error'=>true]));
        }
    }
}