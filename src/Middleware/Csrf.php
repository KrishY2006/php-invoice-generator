<?php

declare(strict_types=1);

namespace App\Middleware;

use RuntimeException;

final class Csrf
{
    public const FIELD_NAME = '_csrf';

    private const SESSION_KEY = '_csrf_token';

    public static function handle(): bool
    {
        self::assertSessionStarted();
        self::token();

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return true;
        }

        $submitted = $_POST[self::FIELD_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!is_string($submitted) || $submitted === '' || !hash_equals(self::token(), $submitted)) {
            self::reject();

            return false;
        }

        return true;
    }

    public static function token(): string
    {
        self::assertSessionStarted();

        if (
            !isset($_SESSION[self::SESSION_KEY])
            || !is_string($_SESSION[self::SESSION_KEY])
            || $_SESSION[self::SESSION_KEY] === ''
        ) {
            return self::rotate();
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function rotate(): string
    {
        self::assertSessionStarted();

        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));

        return $_SESSION[self::SESSION_KEY];
    }

    private static function assertSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('The session must be started before using CSRF protection.');
        }
    }

    private static function reject(): void
    {
        http_response_code(419);
        header('Content-Type: text/html; charset=utf-8');

        echo <<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>419 Page Expired</title>
</head>
<body>
    <h1>419 Page Expired</h1>
    <p>Your session has expired or the CSRF token is invalid. Please go back and try again.</p>
</body>
</html>
HTML;
    }
}
