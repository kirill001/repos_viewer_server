<?php

namespace App\Http\Services;

use App\Commit;
use App\Exceptions\NotFoundException;
use App\Repo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RepoService
{
    private ?object $repository = null;
    private array $commits = [];
    private int $commitsCurrentPage = 1;
    private ?int $commitsLastPage = null;

    /**
     * Imports commits of GitHub repository.
     *
     * @param  string  $author
     * @param  string  $name
     * @return void
     */
    private function importCommits(string $author, string $name) : void
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Laravel',
            'Authorization' => 'Basic ' . config('services.github.key')
        ])
            ->withOptions(['verify' => false])
            ->get('https://api.github.com/repos/'.$author.'/'.$name.'/commits?page=' . $this->commitsCurrentPage);

        $header = $response->header('Link');

        //Get last page number
        if ($header && $this->commitsLastPage === null) {
            $links = explode(',', $header);

            $last_link = array_filter($links, function($link) {
                return Str::contains($link, 'rel="last"');
            });

            $last_link = array_values($last_link);

            if (isset($last_link[0])) {
                $this->commitsLastPage = (int)explode('page=', $last_link[0])[1];
            }
        }

        $this->commits = array_merge($this->commits, $response->json());

        if (count($this->commits) > 1000) {
            $this->saveCommits();
        }


        //Allow to parse all pages only in CLI mode to prevent error with max execution time
        $limit_condition = (app()->runningInConsole() || $this->commitsCurrentPage < 10);

        if ($this->commitsLastPage && $this->commitsLastPage > $this->commitsCurrentPage && $limit_condition) {
            $this->commitsCurrentPage++;

            $this->importCommits($author, $name);
        } else {
            //Save commits on last iteration
            $this->saveCommits();
        }
    }

    /**
     * Saves commits to DB and frees memory.
     *
     * @return void
     */
    private function saveCommits()
    {
        $commits = $commitsData = array_map(function($commit) {
            return [
                'repo_id' => $this->repository->id,
                'sha' => $commit['sha'],
                'url' => $commit['url'],
                'message' => $commit['commit']['message'],
                'date' => $commit['commit']['committer']['date'],
            ];
        }, $this->commits);

        Commit::insertIgnore($commits);

        $this->commits = [];
    }

    /**
     * Imports GitHub repository.
     *
     * @param  string  $author
     * @param  string  $name
     * @return void
     */
    private function importRepository(string $author, string $name) : void
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Laravel',
            'Authorization' => 'Basic ' . config('services.github.key')
        ])
        ->withOptions(['verify' => false])
        ->get('https://api.github.com/users/'.$author.'/repos')
        ->json();

        $repos = array_filter($response, function($repo) use ($name) {
            return mb_strtolower($repo['name']) === mb_strtolower($name);
        });

        $repos = array_values($repos);

        if (!isset($repos[0])) {
            throw new NotFoundException('Repository was not found');
        }

        $repository = $repos[0];

        $this->repository = Repo::where('repo_id', $repository['id'])->first();

        if (!$this->repository) {
            $this->repository = Repo::create([
                'repo_id' => $repository['id'],
                'author' => $repository['owner']['login'],
                'name' => $repository['name'],
                'description' => $repository['description'] ?? '',
                'url' => $repository['url'],
                'stars' => $repository['stargazers_count'],
            ]);
        }
    }

    /**
     * Adds data to DB.
     *
     * @param  string  $author
     * @param  string  $name
     * @return void
     */
    public function import(string $author, string $name) : void
    {
        $this->importRepository($author, $name);

        $this->importCommits($author, $name);
    }
}
