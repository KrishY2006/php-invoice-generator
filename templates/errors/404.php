<?php

declare(strict_types=1);

$message = $message ?? 'The requested page could not be found.';
?>
<div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm">
    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">404</p>
    <h1 class="mt-3 text-2xl font-bold text-slate-900">Page not found</h1>
    <p class="mt-2 text-slate-600"><?= e($message) ?></p>
    <a href="/invoices" class="mt-6 inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
        Back to invoices
    </a>
</div>
