<?php

namespace App\Http\Controllers;

use App\Commit;
use App\Http\Services\RepoService;
use App\Repo;

class RepoController extends Controller {

    protected object $repoService;

    public function __construct(RepoService $repoService)
    {
        $this->repoService = $repoService;
    }

    /**
     * Adds repository to DB.
     *
     * @param  string $author
     * @param  string $name
     * @return string
     */
    public function importRepo(string $author, string $name) : string
    {
        try {
            $repo = $this->repoService->import($author, $name);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        return response()->json($repo);
    }

    /**
     * Returns repository
     *
     * @param  Repo $repo
     * @return string
     */
    public function getRepo(Repo $repo) : string
    {
        return response()->json($repo->load('commits'));
    }

    /**
     * Returns all repositories
     *
     * @return string
     */
    public function allRepos() : string
    {
        $repos = Repo::with('commits')->get();

        return response()->json($repos);
    }

    /**
     * Remove repository
     *
     * @param Repo $repo
     * @return void
     * @throws \Exception
     */
    public function removeRepo(Repo $repo) : void
    {
        $repo->delete();
    }

    /**
     * Removes commits
     *
     * @return void
     */
    public function removeCommits() : void
    {
        $ids = request()->input('ids');

        Commit::whereIn('id', $ids)->delete();
    }
}
