<?php

namespace App\Http\Controllers;

use App\Commit;
use App\Exceptions\NotFoundException;
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
     * @return string
     */
    public function addRepo()
    {
        $author = request()->input('author');
        $name = request()->input('name');

        try {
            $this->repoService->import($author, $name);
        } catch (NotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Repository was not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error'
            ]);
        }
    }

    /**
     * Returns commits
     *
     * @param  Repo $repo
     * @return string
     */
    public function getCommits(Repo $repo)
    {
        $page = request()->input('page', 1);

        $offset = ($page - 1) * 20;

        $total = $repo->commits()->count();

        $commits = $repo->commits()->take(20)->offset($offset)->get();

        return response()->json(['total' => $total, 'commits' => $commits]);
    }

    /**
     * Returns all repositories
     *
     * @return string
     */
    public function allRepos()
    {
        $repos = Repo::orderBy('id', 'desc')->get();

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
