<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostIsPublicRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostIsPublicController extends Controller
{
    public function __invoke(PostIsPublicRequest $request): JsonResponse
    {
        $post_id = $request->validated('post_id');

        $post = Post::query()
            ->where("id", $post_id)
            ->where("public", true)
            ->first();

        if ($post === null) {
            return response()->json([
                'message' => 'Invalid id',
            ], 404);
        }

        return response()->json([
            'is_public' => true,
        ]);
    }
}
