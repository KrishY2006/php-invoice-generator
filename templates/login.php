<?php

declare(strict_types=1);

$error = $error ?? null;
$oldEmail = $oldEmail ?? '';
$csrfToken = $csrfToken ?? '';
$csrfFieldName = $csrfFieldName ?? '_csrf';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In | Invoice Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 px-4 py-12">
    <main class="mx-auto w-full max-w-md">
        <div class="rounded-2xl bg-white p-8 shadow-lg">
            <h1 class="text-2xl font-bold text-slate-900">Invoice Generator</h1>
            <p class="mt-1 text-sm text-slate-500">Sign in to manage your invoices.</p>

            <?php if (is_string($error) && $error !== ''): ?>
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/login" class="mt-6 space-y-5">
                <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="username"
                        required
                        autofocus
                        value="<?= e($oldEmail) ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                >
                    Sign in
                </button>
            </form>
        </div>
    </main>
</body>
</html>
