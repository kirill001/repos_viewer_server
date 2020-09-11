<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GithubService
{
    /**
     * Current page for commits parsing
     *
     * @var int
     */
    private int $page = 0;

    /**
     * The last page for commits parsing
     *
     * @var int|null
     */
    private ?int $lastPage = null;

    /**
     * Parsed commits
     *
     * @var array
     */
    private array $commits = [];

    /**
     * Makes authorized request to GitHub API
     * @param string $url
     * @return object
     */
    private function request(string $url) : object
    {
        return  Http::withHeaders([
            'User-Agent' => 'Laravel',
            'Authorization' => 'Basic ' . config('services.github.key')
        ])
            ->withOptions(['verify' => false])
            ->get('https://api.github.com' . $url);
    }

    /**
     * Parses provided header and returns the last page number
     * @param string $header
     * @return int|void
     */
    private function getLastPage(string $header) : ?int
    {
        $links = explode(',', $header);

        $lastLink = array_filter($links, function($link) {
            return Str::contains($link, 'rel="last"');
        });

        $lastLink = array_values($lastLink);

        if (isset($lastLink[0])) {
            return (int)preg_replace('/^page=/', '', $lastLink[0]);
        }
    }

    /**
     * Finds GitHub repository
     * @param string $author
     * @param string $name
     * @throws NotFoundException
     * @return array|void
     */
    public function findProject(string $author, string $name) : ?array
    {
        $response = $this->request('/users/'.$author.'/repos?page=' . $this->page);

        if ($response->header('Link') && $this->lastPage === null) {
            $this->lastPage = $this->getLastPage($response->header('Link'));
        }

        $projects = array_filter($response->json(), function($repo) use ($name) {
            return mb_strtolower($repo['name']) === mb_strtolower($name);
        });

        $projects = array_values($projects);

        if (isset($projects[0])) {
            return $projects[0];
        }

        if ($this->lastPage > $this->page) {
            throw new NotFoundException('Project was not found');
        } else {
            $this->page++;

            return $this->findProject($author, $name);
        }
    }

    /**
     * Finds commits for provided repository
     * @param Project $project
     * @throws NotFoundException
     * @return array
     */
    public function getCommits(Project $project) : array
    {
        $response = $this->request('/repos/'.$project->author.'/'.$project->name.'/commits?page=' . $this->page);

        if ($response->header('Link') && $this->lastPage === null) {
            $this->lastPage = $this->getLastPage($response->header('Link'));
        }

        $commits = array_map(function($commit) use ($project) {
            return [
                'project_id' => $project->id,
                'sha' => $commit['sha'],
                'url' => $commit['url'],
                'message' => $commit['commit']['message'],
                'date' => $commit['commit']['committer']['date'],
            ];
        }, $response->json());

        $this->commits = array_merge($this->commits, $commits);

        //Allow to parse all pages only in CLI mode to prevent error with max execution time
        $limit_condition = (app()->runningInConsole() || $this->lastPage < 10);

        if ($this->lastPage && $this->lastPage > $this->page && $limit_condition) {
            $this->page++;

            $this->getCommits($project);
        }

        return $this->commits;
    }
}
