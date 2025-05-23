<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectIsInitialized
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has("project_initialized")) {
            if (Project::query()->exists()) {
                session()->put("project_initialized", true);
            } else {
                return redirect()->route("initialize.index");
            }
        }


        return $next($request);
    }
}
