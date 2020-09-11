<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Requests\ProjectRequest;
use App\Repositories\CommitRepository;
use App\Services\GithubService;
use App\Project;
use App\Repositories\ProjectRepository;

class ProjectController extends Controller
{
    private ?object $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * Creates new project and stores commits
     *
     * @param ProjectRequest $request
     * @param GithubService $githubService
     * @return object
    */
    public function store(ProjectRequest $request, GithubService $githubService, CommitRepository $commitRepository) : object
    {
        try {
            $githubProject = $githubService->findProject($request->author, $request->name);

            $project = $this->projectRepository->create($githubProject);

            $commits = $githubService->getCommits($project);

            $commitRepository->save($commits);

            return $project;

        } catch (NotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Server error']);
        }
    }

    /**
     * Returns all repositories
     *
     * @throws \Exception
     * @return object
     */
    public function index() : object
    {
        $projects = $this->projectRepository->all();

        return response()->json($projects);
    }

    /**
     * Remove repository
     *
     * @param Project $project
     * @throws \Exception
     * @return void
     */
    public function destroy(Project $project) : void
    {
        $this->projectRepository->remove($project);
    }
}
