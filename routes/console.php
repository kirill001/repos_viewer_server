<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Http\Services\RepoService;
use App\Repo;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('repos:list', function() {
    $repos = Repo::with('commits')->get();

    if (!count($repos)) {
        exit('There is no repos yet');
    }

    echo 'Imported repositories:' . PHP_EOL;

    $headers = ['Name', 'Stars'];

    $this->table($headers, $repos->map(function ($item) {
        return [$item->author.'/'.$item->name, $item->stars];
    }));
})->describe('Returns list of imported repositories');

Artisan::command('repos:import', function(RepoService $repoService) {
    $author = trim($this->ask('Please enter repository author'));
    $name = trim($this->ask('Please enter repository name'));

    if (!$author || !$name) {
        exit('All arguments are required');
    }

    try {
        $repo = $repoService->import($author, $name);
    } catch (Exception $e) {
        echo 'Import failed' . $e->getMessage();
        exit;
    }

    echo 'Repository "' . $repo->author . '/' . $repo->name . '" has been imported successfully';
})->describe('Imports new repository and/or restore/add missing commits');

Artisan::command('repos:commits', function() {
    $author = trim($this->ask('Please enter repository author'));
    $name = trim($this->ask('Please enter repository name'));

    if (!$author || !$name) {
        exit('All arguments are required');
    }

    $repo = Repo::with('commits')->where('author', $author)->where('name', $name)->first();

    if (!$repo) {
        exit('No such repo. Use repos:import command to import');
    }

    echo 'Commits of "' . $repo->author . '/' . $repo->name . '" repository:' . PHP_EOL;

    $headers = ['SHA', 'Date', 'Message'];

    $this->table($headers, $repo->commits->map(function ($item) {
        return [$item->sha, $item->date, Str::limit($item->message, 50)];
    }));
})->describe('Returns commits of the specified repository');
