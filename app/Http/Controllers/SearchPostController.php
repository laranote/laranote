<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchPostController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->get('query', '');

        $query = trim($query);
        if (strlen($query) > 50) {
            return response()->json(['error' => 'Query too long'], 400);
        }

        if (empty($query)) {
            $posts = Post::query()->select('id', 'title', 'created_at', 'post_id', 'order')
                ->orderBy('order')
                ->orderBy('post_id', 'asc')
                ->get()
                ->toArray();
        } else {
            $posts = Post::query()
                ->select('id', 'title', 'created_at', 'post_id', 'order')
                ->where('title', 'LIKE', '%' . addslashes($query) . '%')
                ->orWhere('created_at', 'LIKE', '%' . addslashes($query) . '%')
                ->orderBy('order')
                ->orderBy('post_id', 'asc')
                ->get()
                ->toArray();
        }

        return response()->json(['posts' => $posts]);
    }
}
