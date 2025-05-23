<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum UserRoles: int
{
    use EnumToArray;

    case ADMIN = 0;
    case EDITOR = 1;
    case VIEWER = 2;
    case DEACTIVATED = 3;

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isEditor(): bool
    {
        return $this === self::EDITOR;
    }

    public function isViewer(): bool
    {
        return $this === self::VIEWER;
    }

    public function isDeactivated(): bool
    {
        return $this === self::DEACTIVATED;
    }
}
