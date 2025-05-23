<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeUserRoleRequest;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UsersPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users', [
            "users" => User::query()
                ->select(['id', 'role', 'email', 'created_at'])
                ->get(),
            "userRoles" => UserRoles::array()
        ]);
    }

    public function store(ChangeUserRoleRequest $request): void
    {
        User::query()->find($request->validated("user_id"))->update([
            "role" => $request->validated("role_id")
        ]);
    }
}
