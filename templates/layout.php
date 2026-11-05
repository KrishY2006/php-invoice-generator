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

$userEmail = \App\Middleware\Auth::userEmail() ?? 'Admin';
$namePart = str_contains($userEmail, '@') ? strstr($userEmail, '@', true) : $userEmail;
$displayName = ucwords(str_replace(['.', '_', '-'], ' ', (string) $namePart));
$displayName = $displayName !== '' ? $displayName : 'Admin';
$userInitial = strtoupper(mb_substr($displayName, 0, 1));

$navItems = [
    'dashboard' => [
        'label' => 'Dashboard',
        'href' => '/dashboard',
        'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
    ],
    'invoices' => [
        'label' => 'Invoices',
        'href' => '/invoices',
        'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
    ],
    'clients' => [
        'label' => 'Clients',
        'href' => '/clients',
        'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    ],
    'settings' => [
        'label' => 'Settings',
        'href' => '/settings',
        'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z',
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?= e($title) ?> | Invoice Generator</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden"></div>

    <aside
        id="app-sidebar"
        class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 text-slate-100 shadow-2xl transition-transform duration-300 ease-out lg:translate-x-0"
        aria-label="Primary navigation"
    >
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-lg shadow-indigo-950/40">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 10.5h7.5M8.25 14.25h4.5M6 3.75h12A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75z" />
                </svg>
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold tracking-tight text-white">Invoice Generator</p>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Billing workspace</p>
            </div>
            <button
                id="sidebar-close"
                type="button"
                class="ml-auto rounded-lg p-2 text-slate-400 hover:bg-white/5 hover:text-white lg:hidden"
                aria-label="Close navigation"
                aria-controls="app-sidebar"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
            <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Menu</p>
            <?php foreach ($navItems as $key => $item): ?>
                <a
                    href="<?= e($item['href']) ?>"
                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition <?= $active === $key
                        ? 'bg-indigo-500/10 text-white ring-1 ring-inset ring-indigo-400/30'
                        : 'text-slate-400 hover:bg-white/5 hover:text-white' ?>"
                    <?= $active === $key ? 'aria-current="page"' : '' ?>
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg <?= $active === $key
                        ? 'bg-indigo-500/15 text-indigo-300'
                        : 'bg-white/5 text-slate-500 group-hover:text-slate-300' ?>">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?= e($item['icon']) ?>" />
                        </svg>
                    </span>
                    <span><?= e($item['label']) ?></span>
                    <?php if ($active === $key): ?>
                        <span class="ml-auto h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3 rounded-xl bg-white/5 p-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-500/20 text-sm font-semibold text-indigo-200 ring-1 ring-inset ring-indigo-400/30">
                    <?= e($userInitial) ?>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white"><?= e($displayName) ?></p>
                    <p class="truncate text-xs text-slate-400"><?= e($userEmail) ?></p>
                </div>
            </div>

            <form method="post" action="/logout" class="mt-3">
                <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-white/10 px-3 py-2 text-sm font-medium text-slate-400 transition hover:border-red-400/30 hover:bg-red-500/10 hover:text-red-300"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-64">
        <header class="sticky top-0 z-20 border-b border-gray-200 bg-white/85 backdrop-blur">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button
                    id="sidebar-open"
                    type="button"
                    class="rounded-xl border border-gray-200 bg-white p-2 text-gray-500 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 lg:hidden"
                    aria-label="Open navigation"
                    aria-controls="app-sidebar"
                    aria-expanded="false"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-400">Workspace</p>
                    <h1 class="truncate text-lg font-semibold tracking-tight text-gray-900"><?= e($title) ?></h1>
                </div>

                <a
                    href="/invoices/new"
                    class="hidden items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:inline-flex"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New invoice
                </a>

                <div class="hidden items-center gap-3 border-l border-gray-200 pl-4 md:flex">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-100">
                        <?= e($userInitial) ?>
                    </span>
                    <div class="min-w-0">
                        <p class="max-w-[10rem] truncate text-sm font-semibold text-gray-900"><?= e($displayName) ?></p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
                <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm" role="status">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><?= e($flashSuccess) ?></span>
                </div>
            <?php endif; ?>

            <?php if (is_string($flashError) && $flashError !== ''): ?>
                <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm" role="alert">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span><?= e($flashError) ?></span>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>

    <script>
        (function () {
            const sidebar = document.getElementById('app-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const openButton = document.getElementById('sidebar-open');
            const closeButton = document.getElementById('sidebar-close');

            if (!sidebar || !overlay || !openButton || !closeButton) {
                return;
            }

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
                openButton.setAttribute('aria-expanded', 'true');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
                openButton.setAttribute('aria-expanded', 'false');
            }

            openButton.addEventListener('click', openSidebar);
            closeButton.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });
        })();
    </script>
</body>
</html>
