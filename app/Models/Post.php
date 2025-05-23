<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\SafeHtml;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{

    protected $fillable = [
        "title",
        "content",
        "user_id",
        "post_id",
        "has_files",
        "order",
        "data",
        "public"
    ];

    protected $casts = [
        'title' => SafeHtml::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Get the parent post of this post.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Get the child posts of this post.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Post::class, 'post_id')->orderBy('order');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function getCollaborationPostName(): string
    {
        return urlencode(get_called_class() . ":" . $this->id);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($post): void {
            // Delete all children files (File model) from storage/app/public/uploads
            $post->files->each(function ($file): void {
                Storage::disk('public')->delete($file->path);
            });
        });
    }
}
