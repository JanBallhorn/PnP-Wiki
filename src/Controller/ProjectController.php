<?php

namespace App\Controller;

use App\Model\Project;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use DateTime;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ProjectController extends Controller
{
    private string $template = 'project.twig';
    public function __construct(private readonly ProjectRepository $projectRepository = new ProjectRepository(), private readonly UserRepository $userRepository = new UserRepository())
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
        $projects->rewind();
        $this->render($this->template, ['main_projects' => $projects->__serialize()]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function create(): void
    {
        $projects = $this->projectRepository->findAll();
        $this->projectRepository->closeDB();
        $projectNames = array();
        if(!empty($projects)){
            foreach($projects as $project){
                $projectNames[] = $project->getName();
            }
        }
        sort($projectNames);
        $this->render('createProject.twig', ['projects' => $projectNames]);
    }

    /**
     * @throws Exception
     */
    public function save(array $projectData): void
    {
        $username = $this->getUsernameFromToken($this->getCookie());
        $user = $this->userRepository->findOneBy('username', $username);
        $this->userRepository->closeDB();
        $parentProject = $this->projectRepository->findOneBy('name', $projectData['parentProject']);
        $project = new Project(0, $projectData['projectName'], $projectData['description'], new DateTime(), $user, new DateTime(), $user, $parentProject, isset($projectData['private']));
        $this->projectRepository->save($project);
        $this->projectRepository->closeDB();
        header("Location: /project");
    }
}