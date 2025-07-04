<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuthType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuthenticationRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticationPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Authentication', [
            'authTypes' => AuthType::array(),
            'magicMkAuthType' => AuthType::MAGIC_MK->value
        ]);
    }

    public function store(StoreAuthenticationRequest $request): void
    {
        $project = Project::query()->first();

        $project->fill($request->validated());
        $project->magicmk_api_key = $request->validated('magicmk_api_key');
        $project->save();
    }
}
