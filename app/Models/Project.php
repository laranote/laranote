<?php

namespace App\Models;

use App\Enums\AuthType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'logo_url',
        'default_user_role',
        'auth_type',
        'has_admin',
        'magicmk_slug',
    ];

    protected $hidden = [
        'gemini_api_key',
        'fal_api_key',
        'openrouter_api_key',
        'magicmk_api_key',
    ];

    protected $casts = [
        'auth_type' => AuthType::class,
        'email_whitelist' => 'array',
        'gemini_api_key' => 'encrypted',
        'fal_api_key' => 'encrypted',
        'openrouter_api_key' => 'encrypted',
        'magicmk_api_key' => 'encrypted'
    ];

    protected $appends = ['logo_full_url', 'has_gemini_key', 'has_fal_key', 'has_openrouter_key', 'has_magicmk_key'];

    public function getLogoFullUrlAttribute()
    {
        if (!$this->logo_url) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_url);
    }

    public function getHasGeminiKeyAttribute(): bool
    {
        return !empty($this->gemini_api_key);
    }

    public function getHasFalKeyAttribute(): bool
    {
        return !empty($this->fal_api_key);
    }

    public function getHasOpenrouterKeyAttribute(): bool
    {
        return !empty($this->openrouter_api_key);
    }

    public function getHasMagicmkKeyAttribute(): bool
    {
        return !empty($this->magicmk_api_key);
    }
}
