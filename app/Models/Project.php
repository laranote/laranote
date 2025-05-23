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
        'magicmk_api_key',
       ];

    protected $casts = [
        'auth_type' => AuthType::class,
        'email_whitelist' => 'array'
    ];

    protected $appends = ['logo_full_url'];

    public function getLogoFullUrlAttribute()
    {
        if (!$this->logo_url) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_url);
    }
}
