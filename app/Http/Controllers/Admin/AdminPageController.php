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
            'userRoles' => UserRoles::array(),
        ]);
    }

    public function store(StoreAdminSettingsRequest $request): void
    {
        $project = Project::query()->first();

        if ($request->hasFile('project_logo')) {
            if ($project->logo_url) {
                Storage::disk('public')->delete($project->logo_url);
            }

            $project->logo_url = $request->file('project_logo')->store('logos', 'public');
        }

        $project->name = $request->validated('project_name');
        $project->default_user_role = $request->validated('default_role');

        $apiKeys = ['gemini_api_key', 'fal_api_key', 'openrouter_api_key'];

        foreach ($apiKeys as $key) {
            $value = $request->validated($key);
            if ($value !== null) {
                $project->{$key} = $value;
            }
        }

        $project->save();

    }

}
