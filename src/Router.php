<?php

declare(strict_types=1);

namespace App;

use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\DashboardController;
use App\Controllers\InvoiceController;
use App\Controllers\InvoiceLifecycleController;
use App\Controllers\PdfController;
use App\Controllers\SettingsController;
use App\Middleware\Auth;
use App\Repositories\SettingsRepository;

final class Router
{
    /**
     * @var array<int, array{
     *     method: string,
     *     regex: string,
     *     params: array<int, string>,
     *     handler: callable,
     *     middleware: array<int, callable>
     * }>
     */
    private array $routes = [];

    public function __construct(private readonly string $basePath)
    {
    }

    public function registerAuthRoutes(AuthController $authController): void
    {
        $this->get('/login', [$authController, 'showLogin']);
        $this->post('/login', [$authController, 'login']);
        $this->post('/logout', [$authController, 'logout']);
    }

    public function registerDashboardRoutes(DashboardController $dashboardController): void
    {
        $middleware = [[Auth::class, 'handle']];

        $this->get('/dashboard', [$dashboardController, 'index'], $middleware);
        $this->get('/dashboard/export.csv', [$dashboardController, 'exportCsv'], $middleware);
    }

    public function registerSettingsRoutes(
        SettingsController $settingsController,
        SettingsRepository $settings,
    ): void {
        $middleware = [[Auth::class, 'handle']];

        $this->get('/settings', [$settingsController, 'show'], $middleware);
        $this->post('/settings', [$settingsController, 'update'], $middleware);

        $this->get('/logo', function () use ($settings): void {
            $profile = $settings->get();
            $filename = basename((string) ($profile['logo_path'] ?? ''));

            if ($filename === '') {
                $this->json(404, [
                    'success' => false,
                    'error' => 'Logo not found',
                ]);

                return;
            }

            $logoDirectory = realpath($this->basePath . '/storage/logos');
            $logoPath = realpath($this->basePath . '/storage/logos/' . $filename);

            if (
                $logoDirectory === false
                || $logoPath === false
                || !str_starts_with($logoPath, $logoDirectory . DIRECTORY_SEPARATOR)
                || !is_file($logoPath)
            ) {
                $this->json(404, [
                    'success' => false,
                    'error' => 'Logo not found',
                ]);

                return;
            }

            $imageInfo = @getimagesize($logoPath);
            $mime = is_array($imageInfo) && isset($imageInfo['mime']) && is_string($imageInfo['mime'])
                ? $imageInfo['mime']
                : null;
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if ($mime === null || !in_array($mime, $allowedMimes, true)) {
                $this->json(404, [
                    'success' => false,
                    'error' => 'Logo not found',
                ]);

                return;
            }

            header('Content-Type: ' . $mime);
            header('Content-Length: ' . (string) filesize($logoPath));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, max-age=3600');

            readfile($logoPath);
        }, $middleware);
    }

    public function registerClientRoutes(ClientController $clientController): void
    {
        $middleware = [[Auth::class, 'handle']];

        $this->get('/clients', [$clientController, 'index'], $middleware);
        $this->get('/clients/new', [$clientController, 'create'], $middleware);
        $this->post('/clients', [$clientController, 'store'], $middleware);
        $this->get('/clients/{id}/edit', [$clientController, 'edit'], $middleware);
        $this->post('/clients/{id}/edit', [$clientController, 'update'], $middleware);
        $this->post('/clients/{id}/delete', [$clientController, 'destroy'], $middleware);
    }

    public function registerInvoiceRoutes(
        InvoiceController $invoiceController,
        InvoiceLifecycleController $lifecycleController,
        PdfController $pdfController,
    ): void {
        $middleware = [[Auth::class, 'handle']];

        $this->get('/invoices', [$invoiceController, 'index'], $middleware);
        $this->get('/invoices/new', [$invoiceController, 'create'], $middleware);
        $this->post('/invoices', [$invoiceController, 'store'], $middleware);
        $this->get('/invoices/{id}', [$invoiceController, 'show'], $middleware);
        $this->post('/invoices/{id}/send', [$lifecycleController, 'send'], $middleware);
        $this->post('/invoices/{id}/pay', [$lifecycleController, 'pay'], $middleware);
        $this->post('/invoices/{id}/void', [$lifecycleController, 'void'], $middleware);
        $this->get('/invoices/{id}/pdf', [$pdfController, 'download'], $middleware);
    }

    public function get(string $path, callable $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    /**
     * @param array<int, callable> $middleware
     */
    public function add(string $method, string $path, callable $handler, array $middleware = []): self
    {
        $compiled = $this->compilePath($path);

        $this->routes[] = [
            'method' => strtoupper($method),
            'regex' => $compiled['regex'],
            'params' => $compiled['params'],
            'handler' => $handler,
            'middleware' => $middleware,
        ];

        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        $path = $this->normalizePath(rawurldecode($path));
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method || preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            array_shift($matches);

            $params = [];

            foreach ($route['params'] as $index => $name) {
                $params[$name] = rawurldecode((string) ($matches[$index] ?? ''));
            }

            foreach ($route['middleware'] as $middleware) {
                if ($middleware() === false) {
                    return;
                }
            }

            ($route['handler'])(...array_values($params));

            return;
        }

        $this->json(404, [
            'success' => false,
            'error' => 'Not Found',
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    /**
     * @return array{regex: string, params: array<int, string>}
     */
    private function compilePath(string $path): array
    {
        $segments = explode('/', $this->normalizePath($path));
        $regexSegments = [];
        $params = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/', $segment, $matches) === 1) {
                $params[] = $matches[1];
                $regexSegments[] = '([^/]+)';
                continue;
            }

            $regexSegments[] = preg_quote($segment, '#');
        }

        return [
            'regex' => '#^' . implode('/', $regexSegments) . '$#',
            'params' => $params,
        ];
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
