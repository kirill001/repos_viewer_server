<?php

namespace App\Console\Commands;

use App\Repositories\CommitRepository;
use App\Repositories\ProjectRepository;
use App\Services\GithubService;
use Illuminate\Console\Command;

class ProjectsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports new repository and/or restore/add missing commits';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Imports new repository and/or restore/add missing commits
     *
     * @param GithubService $githubService
     * @param ProjectRepository $projectRepository
     * @return mixed
     */
    public function handle(GithubService $githubService, ProjectRepository $projectRepository, CommitRepository $commitRepository)
    {
        $author = trim($this->ask('Please enter repository author'));
        $name = trim($this->ask('Please enter repository name'));

        if (!$author || !$name) {
            exit('All arguments are required');
        }

        try {
            $githubProject = $githubService->findProject($author, $name);

            $project = $projectRepository->create($githubProject);

            $commits = $githubService->getCommits($project);

            $commitRepository->save($commits);

            echo 'Repository "' . $author . '/' . $name . '" has been imported successfully';
        } catch (\Exception $e) {
            echo 'Import failed ' . $e->getMessage();
        }
    }
}
