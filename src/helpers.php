<?php

declare(strict_types=1);

use App\Middleware\Csrf;

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('render')) {
    /**
     * @param array<string, mixed> $data
     */
    function render(string $basePath, string $view, array $data = [], string $layout = 'layout'): void
    {
        $viewPath = $basePath . '/templates/' . $view . '.php';
        $layoutPath = $basePath . '/templates/' . $layout . '.php';

        if (!is_file($viewPath)) {
            throw new RuntimeException(sprintf('View not found: %s', $view));
        }

        if (!is_file($layoutPath)) {
            throw new RuntimeException(sprintf('Layout not found: %s', $layout));
        }

        $data += [
            'title' => 'Invoice Generator',
            'active' => '',
            'csrfToken' => Csrf::token(),
            'csrfFieldName' => Csrf::FIELD_NAME,
        ];

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . $path, true, $status);
        exit;
    }
}
