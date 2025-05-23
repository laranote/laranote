<?php

namespace App\Models;

use App\Enums\UserRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'email',
        'role',
        'hocuspocus_token'
    ];

    protected $casts = [
        'role' => UserRoles::class,
        'created_at' => 'datetime:Y M d'
    ];

    protected $hidden = [
        'remember_token',
    ];

}
