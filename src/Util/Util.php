<?php

declare(strict_types=1);

namespace Src\Util;

class Util
{
    public static function isValidId(mixed $id): bool
    {
        return is_numeric($id) && ctype_digit($id);
    }

    public static function isValidEMail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
