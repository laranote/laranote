<?php

namespace App\Http\Controllers;

use App\Actions\GetHocuspocusServerUrl;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicPostController extends Controller
{
    /**
     * The rendering of ShowPublicPost.vue that a public post on the route named "public.post"
     *
     * @param $post_id
     * @return \Inertia\Response
     */
    public function __invoke($post_id): \Inertia\Response
    {
        $post = Post::query()
            ->where("public", true)
            ->where("id", $post_id)
            ->select("id", "title")
            ->firstOrFail();

        return Inertia::render("ShowPublicPost", [
            "post" => $post,
            "collaborationPostName" => $post->getCollaborationPostName(),
            "hocuspocusUrl" => GetHocuspocusServerUrl::execute(),
        ]);
    }
}
