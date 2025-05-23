<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Models\File;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class FilesController extends Controller
{
    /**
     * Handles the post-request from the TipTap FileHandler extension on the
     * route named "files.store" for uploading the image drag and dropped or pasted.
     *
     * @param StoreFileRequest $request
     * @return JsonResponse
     */
    public function __invoke(StoreFileRequest $request): JsonResponse
    {
        $uploadedFile = $request->file('file');

        // Files stored in storage/app/public/uploads
        $path = $uploadedFile->store('uploads', 'public');

        File::query()->create([
            'post_id' => $request->input('post_id'),
            'path' => $path,
            'original_name' => $uploadedFile->getClientOriginalName()
        ]);

        if ($request->input('post_id')) {
            Post::query()->where('id', $request->input('post_id'))
                ->update(['has_files' => true]);
        }

        $url = asset('storage/' . $path);

        return response()->json(['url' => $url]);
    }
}
