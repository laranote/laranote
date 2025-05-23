<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class DocsController extends Controller
{
    /**
     * Renders Docs.vue on the route named "docs"
     *
     * @return Response
     */
    public function __invoke(): Response
    {
        return Inertia::render('Docs', [
            "posts" => Post::query()->select('id', 'title', 'created_at', 'post_id', 'order')
                ->orderBy('order')
                ->orderBy('post_id', 'asc')
                ->get()
                ->toArray(),
            ]);
    }
}
