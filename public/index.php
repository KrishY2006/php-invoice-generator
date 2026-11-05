<?php

declare(strict_types=1);

use App\Config;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\DashboardController;
use App\Controllers\InvoiceController;
use App\Controllers\InvoiceLifecycleController;
use App\Controllers\PdfController;
use App\Controllers\SettingsController;
use App\Database;
use App\Middleware\Csrf;
use App\Repositories\ClientRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\SettingsRepository;
use App\Router;
use App\Services\InvoiceDocument;
use App\Services\InvoiceNumber;
use App\Services\Money;
use App\Services\PdfRenderer;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/helpers.php';

$basePath = dirname(__DIR__);

try {
    $config = Config::load($basePath);

    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || str_starts_with(strtolower($config->appUrl()), 'https://');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_name('invoice_generator_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    if (!Csrf::handle()) {
        return;
    }

    $database = new Database($config->dbPath(), $basePath . '/database/schema.sql');
    $pdo = $database->pdo();

    $settingsRepository = new SettingsRepository($pdo);
    $clientRepository = new ClientRepository($pdo);
    $invoiceRepository = new InvoiceRepository($pdo, new InvoiceNumber($pdo));
    $invoiceDocument = new InvoiceDocument($invoiceRepository, $clientRepository, $settingsRepository);

    $router = new Router($basePath);
    $authController = new AuthController($config);
    $dashboardController = new DashboardController(
        $config,
        $invoiceRepository,
        $settingsRepository
    );
    $settingsController = new SettingsController($config, $settingsRepository);
    $clientController = new ClientController($config, $clientRepository);
    $invoiceController = new InvoiceController(
        $config,
        $invoiceRepository,
        $clientRepository,
        $settingsRepository,
        $invoiceDocument,
        new Money()
    );
    $invoiceLifecycleController = new InvoiceLifecycleController(
        $invoiceRepository,
        $clientRepository,
        $settingsRepository
    );
    $pdfController = new PdfController(
        $config,
        $invoiceDocument,
        new PdfRenderer($basePath)
    );

    $router->get('/health', static function () use ($config, $database, $router): void {
        $database->pdo()->query('SELECT 1');

        $router->json(200, [
            'success' => true,
            'status' => 'ok',
            'message' => 'Invoice generator is healthy.',
            'app' => $config->appUrl(),
        ]);
    });

    $router->registerAuthRoutes($authController);
    $router->registerDashboardRoutes($dashboardController);
    $router->registerSettingsRoutes($settingsController, $settingsRepository);
    $router->registerClientRoutes($clientController);
    $router->registerInvoiceRoutes(
        $invoiceController,
        $invoiceLifecycleController,
        $pdfController
    );

    $router->dispatch(
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        $_SERVER['REQUEST_URI'] ?? '/'
    );
} catch (Throwable $exception) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'error' => $exception->getMessage(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
