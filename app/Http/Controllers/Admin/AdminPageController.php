<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminSettingsRequest;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings', [
            'roles' => UserRoles::array(),
            'project' => Project::query()->first(),
        ]);
    }

    public function store(StoreAdminSettingsRequest $request): void
    {
        $project = Project::query()->first();

        if (!empty($request->validated('remove_logo')) && $project->logo_url) {
            Storage::disk('public')->delete($project->logo_url);
            $project->logo_url = null;
        }

        if ($request->hasFile('project_logo')) {
            if ($project->logo_url) {
                Storage::disk('public')->delete($project->logo_url);
            }

            $path = $request->file('project_logo')->store('logos', 'public');
            $project->logo_url = $path;
        }

        $project->name = $request->validated('project_name');
        $project->default_user_role = $request->validated('default_role');

        // Handle API keys - update if provided, clear if explicitly set to 'CLEAR_KEY'
        $geminiKey = $request->validated('gemini_api_key');
        if ($geminiKey === 'CLEAR_KEY') {
            $project->gemini_api_key = null;
        } elseif (!empty($geminiKey)) {
            $project->gemini_api_key = $geminiKey;
        }

        $falKey = $request->validated('fal_api_key');
        if ($falKey === 'CLEAR_KEY') {
            $project->fal_api_key = null;
        } elseif (!empty($falKey)) {
            $project->fal_api_key = $falKey;
        }

        $openrouterKey = $request->validated('openrouter_api_key');
        if ($openrouterKey === 'CLEAR_KEY') {
            $project->openrouter_api_key = null;
        } elseif (!empty($openrouterKey)) {
            $project->openrouter_api_key = $openrouterKey;
        }

        $project->save();

    }

}
