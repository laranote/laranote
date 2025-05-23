<?php

namespace App\Enums;

use App\Traits\EnumToArray;

enum AuthType: int
{
    use EnumToArray;

    case MAGIC_MK = 0;
    case STANDARD_LOGIN = 1;

    public function isMagicLogin(): bool
    {
        return $this === self::MAGIC_MK;
    }

    public function isStandardLogin(): bool
    {
        return $this === self::STANDARD_LOGIN;
    }

}
