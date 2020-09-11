<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;

class ProjectsCommits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Returns list of imported projects';

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
     * Returns list of imported projects
     *
     * @return mixed
     */
    public function handle()
    {
        $projects = Project::with('commits')->get();

        if (!count($projects)) {
            exit('There is no projects yet');
        }

        echo 'Imported projects:' . PHP_EOL;

        $headers = ['Name', 'Stars'];

        $this->table($headers, $projects->map(function ($item) {
            return [$item->author.'/'.$item->name, $item->stars];
        }));
    }
}
