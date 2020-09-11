<?php

namespace App\Http\Middleware;

use App\Commit;
use App\Project;
use Closure;

class CheckRelations
{
    /**
     * Check that provided commits belong to provided project
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $id = $request->route('project');
        $project = Project::findOrFail($id);

        $commits = Commit::whereIn('id', $request->ids)->where('project_id', '!=', $project->id)->exists();

        if ($commits) {
            abort(403);
        }

        return $next($request);
    }
}
