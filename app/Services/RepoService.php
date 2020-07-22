<?php

namespace App\Http\Services;

use App\Commit;
use App\Repo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Mockery\Exception;

class RepoService
{
    /**
     * Returns GitHub repository.
     *
     * @param  string  $author
     * @param  string  $name
     * @return array
     */
    public function getRepo(string $author, string $name) : array
    {
        $response = Http::withHeaders(['User-Agent' => 'Laravel'])
            ->withOptions(['verify' => false])
            ->get('https://api.github.com/users/'.$author.'/repos')
            ->json();

        $repos = array_filter($response, function($repo) use ($name) {
            return mb_strtolower($repo['name']) === mb_strtolower($name);
        });

        return $repos[0];
    }

    /**
     * Returns commits of GitHub repository;
     *
     * @param  string  $author
     * @param  string  $name
     * @return array
     */
    public function getCommits(string $author, string $name) : array
    {
        return Http::withHeaders(['User-Agent' => 'Laravel'])
            ->withOptions(['verify' => false])
            ->get('https://api.github.com/repos/'.$author.'/'.$name.'/commits')
            ->json();
    }

    /**
     * Adds data to DB.
     *
     * @param  string  $author
     * @param  string  $name
     * @return object
     */
    public function import(string $author, string $name) : object
    {
        $repository = $this->getRepo($author, $name);
        $commits    = $this->getCommits($author, $name);

        $repo = Repo::where('repo_id', $repository['id'])->first();

        if (!$repo) {
            $repo = Repo::create([
                'repo_id' => $repository['id'],
                'author' => $repository['owner']['login'],
                'name' => $repository['name'],
                'description' => $repository['description'],
                'url' => $repository['url'],
                'stars' => $repository['stargazers_count'],
            ]);
        }

        $commitsData = array_map(function($commit) use ($repo) {
            return [
                'repo_id' => $repo->id,
                'sha' => $commit['sha'],
                'url' => $commit['url'],
                'message' => $commit['commit']['message'],
                'date' => $commit['commit']['committer']['date'],
            ];
        }, $commits);

        Commit::insertIgnore($commitsData);

        return Repo::with('commits')->find($repo->id);
    }
}
