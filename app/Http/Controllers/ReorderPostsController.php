<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePostOrderRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ReorderPostsController extends Controller
{
    public function __invoke(UpdatePostOrderRequest $request): RedirectResponse
    {

        foreach ($request->orders as $order) {
            Post::query()->where('id', $order['id'])->update([
                'order' => $order['order'],
                'post_id' => $order['post_id']
            ]);
        }

        return redirect()->back();
    }
}
