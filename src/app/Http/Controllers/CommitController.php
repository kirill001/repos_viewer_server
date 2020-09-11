<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Requests\ProjectRequest;
use App\Repositories\CommitRepository;
use App\Services\GithubService;
use App\Project;
use App\Repositories\ProjectRepository;

class CommitController extends Controller
{
    private ?object $commitRepository;

    public function __construct(CommitRepository $commitRepository)
    {
        $this->commitRepository = $commitRepository;
    }

    /**
     * Returns commits
     *
     * @param Project $project
     * @throws \Exception
     * @return object
     */
    public function index(Project $project) : object
    {
        $page = request()->input('page', 1);

        $data = $this->commitRepository->paginate($project, $page, 20);

        return response()->json(['data' => $data]);
    }

    /**
     * Removes commits
     *
     * @throws \Exception
     * @return void
     */
    public function destroy(Project $project) : void
    {
        $this->authorize('delete', $project);
        $ids = request()->input('ids');

        $this->commitRepository->delete($ids);
    }
}
