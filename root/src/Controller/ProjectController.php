<?php

namespace App\Controller;

use App\Collection\UserCollection;
use App\Model\Project;
use App\Model\User;
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
        $projects = $this->projectRepository->findBy('parent_project', null, 'name');
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
        $authorized = null;
        if(isset($projectData['private'])){
            $private = true;
            $authorized = new UserCollection();
            $authorized->rewind();
            $authorized->offsetSet($authorized->key(), $user);
            if(isset($projectData['authorized'])){
                foreach ($projectData['authorized'] as $userId){
                    $authorized->offsetSet($authorized->key(), $this->userRepository->findById($userId));
                    $authorized->next();
                }
            }
        }
        if($sameProject === null && ($parentProject !== null || $projectData['parentProject'] === '')) {
            $project = new Project(0, $projectData['name'], $projectData['description'], new DateTime(), $user, new DateTime(), $user, $parentProject, $private, $authorized, 0);
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
        $project = $this->projectRepository->findById($project['id']);
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
        $project = $this->projectRepository->findById($project['id']);
        $this->render("editProject.twig", ['projects' => $projects->__serialize(), 'project' => $project]);
    }

    /**
     * @throws Exception
     */
    public function update(array $projectData): void
    {
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $project = $this->projectRepository->findById($projectData['id']);
        $parentProject = $this->projectRepository->findOneBy('name', $projectData['parentProject']);
        $sameProject = $this->projectRepository->findOneBy('name', $projectData['name']);
        $oldPrivateState = $project->getPrivate();
        $oldAuthorizedState = $project->getAuthorized();
        $private = false;
        $authorized = null;
        $authorizedComparison = false;
        if(isset($projectData['private']) && ($projectData['private'] === true || $projectData['private'] === "private")){
            $private = true;
            $authorized = new UserCollection();
            $authorized->rewind();
            $authorized->offsetSet($authorized->key(), $user);
            if(isset($projectData['authorized'])){
                if(gettype($projectData['authorized']) === "array"){
                    foreach ($projectData['authorized'] as $userId){
                        $authorized->next();
                        $authorized->offsetSet($authorized->key(), $this->userRepository->findById($userId));
                    }
                }
                else if($parentProject !== null && $parentProject->getAuthorized() !== null){
                    $authorized = $parentProject->getAuthorized();
                }
            }
        }
        if($oldAuthorizedState !== null && $authorized !== null){
            $comparison1 = empty(array_udiff($authorized->__serialize(), $oldAuthorizedState->__serialize(), function($a, $b) {
                return $a->getId() - $b->getId();
            }));
            $comparison2 = empty(array_udiff($oldAuthorizedState->__serialize(), $authorized->__serialize(), function($a, $b) {
                return $a->getId() - $b->getId();
            }));
            if($comparison1 && $comparison2){
                $authorizedComparison = true;
            }
        }
        else if($oldAuthorizedState === null && $authorized === null){
            $authorizedComparison = true;
        }
        $project->setName($projectData['name']);
        $project->setDescription($projectData['description']);
        $project->setParentProject($parentProject);
        $project->setPrivate($private);
        $project->setAuthorized($authorized);
        if(($sameProject === null || $sameProject->getId() === $project->getId()) && ($parentProject !== null || $projectData['parentProject'] === '')) {
            $this->projectRepository->save($project);
            if($oldPrivateState !== $private || !$authorizedComparison){
                $articles = $this->articleRepository->findBy('project', $project->getId());
                if($articles !== null){
                    $articles->rewind();
                    for($i = 0; $i < $articles->count(); $i++){
                        $article = $articles->current();
                        $article->setPrivate($private);
                        $article->setAuthorized($authorized);
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
                            'private' => $private,
                            'authorized' => $authorized
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
    public function delete(array $project): void
    {
        $project = $this->projectRepository->findById($project['id']);
        if($project->getChildren() === null){
            $this->projectRepository->delete($project);
            header("Location: /project");
        }
        else{
            header("Location: /project/detail?" . http_build_query(['id'=>$project->getId(), 'error'=>true]));
        }
    }
}