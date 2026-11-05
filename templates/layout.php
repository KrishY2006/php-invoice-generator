<?php

declare(strict_types=1);

$title = $title ?? 'Invoice Generator';
$active = $active ?? '';
$content = $content ?? '';
$csrfToken = $csrfToken ?? \App\Middleware\Csrf::token();
$csrfFieldName = $csrfFieldName ?? \App\Middleware\Csrf::FIELD_NAME;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$navItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => '/dashboard'],
    'invoices' => ['label' => 'Invoices', 'href' => '/invoices'],
    'clients' => ['label' => 'Clients', 'href' => '/clients'],
    'settings' => ['label' => 'Settings', 'href' => '/settings'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | Invoice Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100">
    <header class="bg-white shadow-sm">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4">
            <a href="/dashboard" class="text-lg font-semibold text-slate-900">Invoice Generator</a>

            <nav class="flex flex-wrap items-center gap-1">
                <?php foreach ($navItems as $key => $item): ?>
                    <a
                        href="<?= e($item['href']) ?>"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition <?= $active === $key ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"
                    >
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>

                <form method="post" action="/logout" class="ml-1">
                    <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
                    <button
                        type="submit"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-red-50 hover:text-red-700"
                    >
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        <?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                <?= e($flashSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if (is_string($flashError) && $flashError !== ''): ?>
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <?= e($flashError) ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</body>
</html>
