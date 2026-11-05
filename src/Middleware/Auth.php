<?php

declare(strict_types=1);

namespace App\Middleware;

final class Auth
{
    public const SESSION_KEY = 'admin_email';

    public static function handle(): bool
    {
        if (self::check()) {
            return true;
        }

        header('Location: /login', true, 302);

        return false;
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY])
            && is_string($_SESSION[self::SESSION_KEY])
            && $_SESSION[self::SESSION_KEY] !== '';
    }

    public static function userEmail(): ?string
    {
        return self::check() ? $_SESSION[self::SESSION_KEY] : null;
    }
}
