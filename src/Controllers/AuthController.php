<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Middleware\Auth;
use App\Middleware\Csrf;

final class AuthController
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900;

    public function __construct(private readonly Config $config)
    {
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');

            return;
        }

        $error = $_SESSION['login_error'] ?? null;
        $oldEmail = $_SESSION['login_old_email'] ?? '';
        unset($_SESSION['login_error'], $_SESSION['login_old_email']);

        $csrfToken = Csrf::token();
        $csrfFieldName = Csrf::FIELD_NAME;

        http_response_code(200);
        header('Content-Type: text/html; charset=utf-8');

        require $this->config->basePath() . '/templates/login.php';
    }

    public function login(): void
    {
        $now = time();
        $lockedUntil = (int) ($_SESSION['login_locked_until'] ?? 0);

        if ($lockedUntil > $now) {
            $_SESSION['login_error'] = sprintf(
                'Too many failed login attempts. Please try again in %d seconds.',
                $lockedUntil - $now
            );

            $this->redirect('/login');

            return;
        }

        if ($lockedUntil > 0) {
            unset($_SESSION['login_locked_until'], $_SESSION['login_attempts']);
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->recordFailedAttempt('Email and password are required.', $email);

            return;
        }

        $emailMatches = strcasecmp($email, $this->config->adminEmail()) === 0;
        $passwordMatches = password_verify($password, $this->config->adminPasswordHash());

        if ($emailMatches && $passwordMatches) {
            session_regenerate_id(true);

            $_SESSION[Auth::SESSION_KEY] = $this->config->adminEmail();
            $_SESSION['authenticated_at'] = $now;

            unset(
                $_SESSION['login_attempts'],
                $_SESSION['login_locked_until'],
                $_SESSION['login_error'],
                $_SESSION['login_old_email']
            );

            Csrf::rotate();

            $this->redirect('/dashboard');

            return;
        }

        $this->recordFailedAttempt('Invalid email or password.', $email);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();

        $this->redirect('/login');
    }

    private function recordFailedAttempt(string $message, string $email): void
    {
        $attempts = (int) ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['login_attempts'] = $attempts;
        $_SESSION['login_old_email'] = $email;

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION['login_locked_until'] = time() + self::LOCKOUT_SECONDS;
            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_error'] = 'Too many failed login attempts. Please try again later.';
        } else {
            $_SESSION['login_error'] = sprintf(
                '%s %d attempt(s) remaining.',
                $message,
                self::MAX_LOGIN_ATTEMPTS - $attempts
            );
        }

        $this->redirect('/login');
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path, true, 302);
    }
}
