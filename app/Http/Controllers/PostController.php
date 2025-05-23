<?php

namespace App\Http\Controllers;

use App\Actions\GetHocuspocusServerUrl;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Adding the last viewed post to a cookie for later use in the HomepageController::class
     *
     * @param Post $post
     * @return void
     */
    private function updateLastViewedPost(Post $post): void
    {
        Cookie::queue('last_viewed_post', $post->id, 60 * 24 * 30); // 30 days
    }

    /**
     * POST request on the resource route "posts.store"
     * Creating a post in the database, data validated in the StorePostRequest::class
     * appending the user id for the "user_id" field in the Post model.
     *
     * @param StorePostRequest $request
     * @return RedirectResponse
     */
    public function store(StorePostRequest $request): RedirectResponse
    {

        $post = Post::query()->create(array_merge(
            $request->validated(),
            [
                'user_id' => Auth::id(),
                'order' => (Post::query()->max('order') ?? 0) + 1
            ]
        ));

        $this->updateLastViewedPost($post);

        return Redirect::route("posts.show", $post);
    }

    /**
     * GET request on the resource route "posts.show"
     * Rendering the ShowPost component with the things we need
     *
     * @param Post $post
     * @param Request $request
     * @return Response
     */
    public function show(Post $post, Request $request): Response
    {
        $this->updateLastViewedPost($post);

        return Inertia::render("ShowPost", [
            "posts" => Post::query()->select('id', 'title', 'created_at', 'post_id', 'order')
                ->orderBy('order')
                ->orderBy('post_id', 'asc')  // Group children with their parents
                ->get()
                ->toArray(),
            "post" => [
                'id' => $post->id,
                'title' => $post->title,
                'public' => $post->public,
            ],
            "collaborationPostName" => $post->getCollaborationPostName(),
            "hocuspocusUrl" => GetHocuspocusServerUrl::execute(),
            "hocuspocusToken" => $request->user()->hocuspocus_token
        ]);
    }

    /**
     * PUT/PATCH request on the resource route "posts.update"
     * Updating an existing posts title or its public attribute
     *
     * @param Post $post
     * @param StorePostRequest $request
     */
    public function update(Post $post, StorePostRequest $request): void
    {
        $post->update($request->validated());
    }

    /**
     * DELETE request on the resource route "posts.destroy"
     *
     * @param Post $post
     * @return RedirectResponse
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->children()->update(['post_id' => null]);

        $post->delete();
        return Redirect::route("homepage");
    }


}
