<?php

namespace App\Http\Controllers;

use App\Enums\UserRoles;
use App\Models\Project;
use App\Enums\AuthType;
use App\Http\Requests\StoreProjectRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class InitializeProjectController extends Controller
{
    /**
     * Renders the page for creating the initial "Project" entries in the db
     * used for easing the process of starting "Laranote".
     *
     * @return \Inertia\Response
     */
    public function index(): \Inertia\Response
    {
        return Inertia::render('InitializeProject', [
            'authTypes' => AuthType::array(),
            'userRoles' => UserRoles::array(),
            'magicMkAuthType' => AuthType::MAGIC_MK,
            'userViewerRole' => UserRoles::VIEWER,
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $data = $validated;

        if ($request->hasFile('logo')) {
            $data['logo_url'] = $request->file('logo')->store('logos', 'public');
        }

        if (isset($data['email_whitelist'])) {
            $data['email_whitelist'] = json_encode($data['email_whitelist']);
        }

        unset($data['logo']);

        Project::query()->create($data);

        return redirect()->route('homepage');
    }
}
