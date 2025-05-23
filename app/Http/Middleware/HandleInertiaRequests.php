<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $userRoles = [];
        if ($request->user()) {
            $userRoles = [
                "is_admin" => $request->user()->role->isAdmin(),
                "is_editor" => $request->user()->role->isEditor(),
                "is_viewer" => $request->user()->role->isViewer()
            ];
        }

        return array_merge(parent::share($request), $userRoles, [
            'project' => Project::query()->first(),
            'auth' => [
                'user' => $request->user() ? $request->user()->only('id', 'email', 'role') : null,
            ],
        ]);
    }
}
