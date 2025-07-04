<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClearApiKeyRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClearApiKeyController extends Controller
{
    public function __invoke(ClearApiKeyRequest $request): void
    {
        $project = Project::query()->first();

        if ($project) {
            $keyType = $request->validated('key_type');

            switch ($keyType) {
                case 'gemini':
                    $project->gemini_api_key = null;
                    break;
                case 'fal':
                    $project->fal_api_key = null;
                    break;
                case 'openrouter':
                    $project->openrouter_api_key = null;
                    break;
                case 'magicmk':
                    $project->magicmk_api_key = null;
                    break;
            }

            $project->save();
        }

    }
}
