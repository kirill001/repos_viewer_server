<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ProjectsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:commits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Returns commits of the specified project';

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
     * Returns commits of the specified project
     *
     * @return mixed
     */
    public function handle()
    {
        $author = trim($this->ask('Please enter repository author'));
        $name = trim($this->ask('Please enter repository name'));

        if (!$author || !$name) {
            exit('All arguments are required');
        }

        $project = Project::with('commits')->where('author', $author)->where('name', $name)->first();

        if (!$project) {
            exit('No such project. Use project:import command to import');
        }

        echo 'Commits of "' . $project->author . '/' . $project->name . '" repository:' . PHP_EOL;

        $headers = ['SHA', 'Date', 'Message'];

        $this->table($headers, $project->commits->map(function ($item) {
            return [$item->sha, $item->date, Str::limit($item->message, 50)];
        }));
    }
}
