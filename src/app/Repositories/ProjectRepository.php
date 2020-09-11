<?php

namespace App\Repositories;

use App\Commit;
use App\Project;

class ProjectRepository
{
    /**
     * Creates new project
     * @param array $githubProject
     * @throws \Exception
     * @return \App\Project
     */
    public function create(array $githubProject) : object
    {
        $project = Project::where('project_id', $githubProject['id'])->first();

        if (!$project) {
            $project = Project::create([
                'project_id' => $githubProject['id'],
                'author' => $githubProject['owner']['login'],
                'name' => $githubProject['name'],
                'description' => $githubProject['description'] ?? '',
                'url' => $githubProject['url'],
                'stars' => $githubProject['stargazers_count'],
            ]);
        }

        return $project;
    }

    /**
     * Removes an existing project
     * @param Project $project
     * @throws \Exception
     * @return void
     */
    public function remove(Project $project) : void
    {
        $project->delete();
    }

    /**
     * Returns all existing projects
     * @throws \Exception
     * @return object
     */
    public function all() : object
    {
        return Project::orderBy('id', 'desc')->get();
    }

    /**
     * Returns paginated projects
     * @param Project $project
     * @param int $page
     * @param int $limit
     * @throws \Exception
     * @return array
     */
    public function paginate(Project $project, int $page, int $limit) : array
    {
        $offset = ($page - 1) * $limit;

        $total = $project->commits()->count();

        $commits = $project->commits()->get()->take($limit)->offset($offset)->get();

        return [
            'total' => $total,
            'commits' => $commits
        ];
    }
}
