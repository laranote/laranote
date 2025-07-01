<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class RemoveLogoController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $project = Project::query()->first();

        if ($project && $project->logo_url) {
            Storage::disk('public')->delete($project->logo_url);
            $project->logo_url = null;
            $project->save();
        }

        return redirect()->route('admin.index');
    }
}
