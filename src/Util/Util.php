<?php

declare(strict_types=1);

namespace Src\Util;

class Util
{
    public static function isValidId(mixed $id): bool
    {
        return filter_var($id, FILTER_VALIDATE_INT) !== false;
    }

    public static function isValidEMail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
