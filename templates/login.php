<?php

declare(strict_types=1);

$error = $error ?? null;
$oldEmail = $oldEmail ?? '';
$csrfToken = $csrfToken ?? '';
$csrfFieldName = $csrfFieldName ?? '_csrf';

$inputClass = 'mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Sign In | Invoice Generator</title>
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
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="relative hidden overflow-hidden bg-slate-950 p-10 text-white lg:flex lg:flex-col lg:justify-between xl:p-14">
            <div class="absolute -right-24 top-16 h-72 w-72 rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-20 h-80 w-80 rounded-full bg-violet-500/10 blur-3xl"></div>

            <div class="relative">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-950/50">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 10.5h7.5M8.25 14.25h4.5M6 3.75h12A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold tracking-tight">Invoice Generator</p>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">Billing workspace</p>
                    </div>
                </div>

                <div class="mt-20 max-w-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-300">Smarter billing</p>
                    <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight xl:text-5xl">
                        Invoices that look as polished as your business.
                    </h1>
                    <p class="mt-5 text-base leading-7 text-slate-400">
                        Create, send, and track invoices from one calm workspace built for independent professionals.
                    </p>
                </div>
            </div>

            <div class="relative mt-16 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm font-semibold text-white">Clear cash flow</p>
                    <p class="mt-1 text-xs leading-5 text-slate-400">See outstanding, paid, and overdue totals at a glance.</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm font-semibold text-white">Professional PDFs</p>
                    <p class="mt-1 text-xs leading-5 text-slate-400">Download branded invoices whenever you need them.</p>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center px-4 py-12 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 10.5h7.5M8.25 14.25h4.5M6 3.75h12A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Invoice Generator</p>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-400">Billing workspace</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl shadow-gray-200/50">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Welcome back</h1>
                        <p class="mt-2 text-sm text-gray-500">Sign in to manage your invoices and clients.</p>
                    </div>

                    <?php if (is_string($error) && $error !== ''): ?>
                        <div class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <span><?= e($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/login" class="mt-6 space-y-5">
                        <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700">Email address</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="username"
                                required
                                autofocus
                                value="<?= e($oldEmail) ?>"
                                class="<?= e($inputClass) ?>"
                                placeholder="you@company.com"
                            >
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="<?= e($inputClass) ?>"
                                placeholder="Enter your password"
                            >
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Sign in
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-gray-400">
                    Secure admin access for your billing workspace.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
