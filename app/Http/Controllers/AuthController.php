<?php

namespace App\Http\Controllers;

use App\Enums\AuthType;
use App\Enums\UserRoles;
use App\Http\Requests\StandardLoginRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{

    /**
     * Handles login entry point.
     * If using Magic.mk auth, validates token via external API and logs in the user.
     * Otherwise, shows the standard login form.
     */
    public function index(): Application|Response|Redirector|RedirectResponse
    {
        $project = Project::query()->first();

        if ($project->auth_type === AuthType::MAGIC_MK) {
            if (request()->query(key: "token")) {
                $response = Http::withHeaders([
                    'X-API-Key' => $project->magicmk_api_key,
                ])->post('https://magic.mk/api/request_validated/', [
                    'request_id' => request()->query("request_id"),
                ]);

                if ($response->successful()) {
                    $email = $response->json() ["email"];
                    $this->register_or_login_user($email, $project);

                    request()->session()->regenerate();

                    return redirect('/');
                }
            }
            return Inertia::render('MagicLogin', [
                "projectSlug" => $project->magicmk_slug,
                "redirectUrl" => config("app.url") . "/login",
            ]);
        }

        return Inertia::render('StandardLogin');
    }

    /**
     * Handles standard email/password login.
     * Validates credentials against users stored in the .users file.
     * Logs the user in and redirects to homepage if successful.
     */
    public function login(StandardLoginRequest $request): RedirectResponse
    {
        if (!file_exists(base_path('.users'))) {
            return redirect()->route("login");
        }

        $users = json_decode(file_get_contents(base_path('.users')), true);

        foreach ($users as $user) {
            if ($user['email'] === $request->validated('email') &&
                password_verify($request->validated('password'), $user['password'])) {
                $project = Project::query()->first();
                $this->register_or_login_user($request->validated('email'), $project);

                $request->session()->regenerate();

                return redirect()->route("homepage");
            }
        }

        return redirect()->route("login");
    }

    /**
     * Logs in or creates a user with a given email and assigns a role.
     * If no admin exists yet, assigns an admin role and updates the project.
     * Also regenerates the user’s hocuspocus_token.
     */
    public function register_or_login_user(string $email, Project $project): void
    {
        if ($project->has_admin) {
            $role = $project->default_user_role;
        } else {
            $role = UserRoles::ADMIN;
            $project->update(["has_admin" => true]);
        }

        $user = User::query()->firstOrCreate(
            [
                "email" => $email
            ],
            [
                "email" => $email,
                "role" => $role,
                'hocuspocus_token' => Str::random(30)
            ]
        );

        $user->update(['hocuspocus_token' => Str::random(30)]);

        Auth::login($user, true);
    }


    public function logout(Request $request): Application|Redirector|RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(route("login"));
    }
}
