<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupUnusedFiles extends Command
{
    protected $signature = 'app:cleanup-unused-files';

    protected $description = 'Clean up attached files that are no longer referenced in post content';

    /**
     * - Processes posts in chunks using `chunkById()` to reduce memory usage and support large datasets.
     * - Deletes files from the database in bulk using `File::whereIn()->delete()` to minimize database queries.
     */
    public function handle(): void
    {
        $this->info('Starting cleanup of unused files...');

        $deletedCount = 0;

        Post::query()->where('has_files', true)
            ->with('files')
            ->chunkById(100, function ($posts) use (&$deletedCount) {
                foreach ($posts as $post) {
                    $content = is_string($post->data) ? $post->data : (string)$post->data;

                    $unusedFileIds = collect();

                    $post->files->each(function ($file) use ($content, &$unusedFileIds) {
                        $filePath = $file->path; // @phpstan-ignore-line
                        $fileName = basename($filePath);

                        if (!str_contains($content, $fileName) && !str_contains($content, $filePath)) {
                            Storage::disk('public')->delete($filePath);
                            $unusedFileIds->push($file->id); // @phpstan-ignore-line
                        }
                    });

                    if ($unusedFileIds->isNotEmpty()) {
                        File::query()->whereIn('id', $unusedFileIds)->delete();
                        $deletedCount += $unusedFileIds->count();
                    }

                    // Check if any files remain
                    if ($post->files->count() === $unusedFileIds->count()) {
                        $post->update(['has_files' => false]);
                    }
                }
            });

        $this->info("Cleanup completed. Removed {$deletedCount} unused files.");
    }
}
