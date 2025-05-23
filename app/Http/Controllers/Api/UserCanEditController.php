<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserCanEditRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserCanEditController extends Controller
{
    /**
     * Is the user allowed to edit
     *
     * @param UserCanEditRequest $request
     * @return JsonResponse
     */
    public function __invoke(UserCanEditRequest $request): JsonResponse
    {
        $token = $request->validated('token');

        $user = User::query()->where('hocuspocus_token', $token)->first();

        if ($user === null) {
            return response()->json([
                'message' => 'Invalid token',
            ], 404);
        }

        return response()->json([
            'can_edit' => !($user->role->isViewer() or $user->role->isDeactivated()),
        ]);
    }
}
