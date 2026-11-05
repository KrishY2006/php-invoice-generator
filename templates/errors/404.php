<?php

declare(strict_types=1);

$message = $message ?? 'The requested page could not be found.';
?>
<div class="flex min-h-[60vh] items-center justify-center">
    <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white px-8 py-12 text-center shadow-sm">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
            </svg>
        </span>
        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600">404 error</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900">Page not found</h1>
        <p class="mt-3 text-sm leading-6 text-gray-500"><?= e($message) ?></p>
        <a
            href="/invoices"
            class="mt-7 inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to invoices
        </a>
    </div>
</div>
