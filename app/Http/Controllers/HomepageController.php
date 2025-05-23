<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class HomepageController extends Controller
{
    /**
     * GET "/"
     * If there is a last_viewed_post cookie, and that post exists redirect to it
     * if it no longer exists remove the cookie.
     *
     * If there is no cookie or the post in it does not exist, try to redirect to the first post.
     * Finally, if there are no posts, render the welcome page
     *
     * @param Request $request
     * @return Response|RedirectResponse
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $lastViewedPostId = $request->cookie('last_viewed_post');

        if ($lastViewedPostId) {
            $post = Post::query()->find($lastViewedPostId);
            if ($post) {
                return Redirect::route('posts.show', ['post' => $post]);
            }

            Cookie::queue(Cookie::forget('last_viewed_post'));
        }

        $post = Post::query()->first();
        if ($post) {
            return Redirect::route('posts.show', ['post' => $post]);
        }

        return Inertia::render('Welcome', [
            "posts" => Post::query()->select('id', 'title', 'created_at', 'post_id', 'order')
                ->orderBy('order')
                ->orderBy('post_id', 'asc')
                ->get()
                ->toArray(),
        ]);
    }
}
