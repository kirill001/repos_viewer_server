<?php

namespace App\Repositories;

use App\Commit;
use App\Project;

class CommitRepository
{
    /**
     * Returns paginated commits
     * @param Project $project
     * @param int $page
     * @param int $limit
     * @throws \Exception
     * @return array
     */
    public function paginate(Project $project, int $page, int $limit) : array
    {
        $offset = ($page - 1) * 20;

        $total = $project->commits()->count();

        $commits = $project->commits()->take($limit)->offset($offset)->get();

        return [
            'total' => $total,
            'commits' => $commits
        ];
    }

    /**
     * Saves commits to DB
     * @param array|integer $commits
     * @throws \Exception
     * @return void
     */
    public function save($commits) : void
    {
        if (gettype($commits) !== 'array') {
            $commits = (array)$commits;
        }

        Commit::insertIgnore($commits);
    }

    /**
     * Removes commits
     * @param array|integer $ids
     * @throws \Exception
     * @return void
     */
    public function delete($ids) : void
    {
        if (gettype($ids) !== 'array') {
            $ids = (array)$ids;
        }

        Commit::whereIn('id', $ids)->delete();
    }
}
