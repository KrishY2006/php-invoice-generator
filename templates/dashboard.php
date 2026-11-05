<?php

declare(strict_types=1);

$summary = $summary ?? [];
$currency = strtoupper((string) ($currency ?? 'USD'));
$outstandingCents = (int) ($summary['outstanding_cents'] ?? 0);
$outstandingCount = (int) ($summary['outstanding_count'] ?? 0);
$paidThisMonthCents = (int) ($summary['paid_this_month_cents'] ?? 0);
$paidThisMonthCount = (int) ($summary['paid_this_month_count'] ?? 0);
$overdueCents = (int) ($summary['overdue_cents'] ?? 0);
$overdueCount = (int) ($summary['overdue_count'] ?? 0);

$formatMoney = static function (int $cents) use ($currency): string {
    return $currency . ' ' . number_format($cents / 100, 2);
};

$pluralize = static function (int $count, string $singular, string $plural): string {
    return $count === 1 ? $singular : $plural;
};
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">Financial overview</p>
            <p class="mt-1 max-w-2xl text-sm text-gray-600">
                A snapshot of your billing activity, open balances, and payments received this month.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="/dashboard/export.csv"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export CSV
            </a>
            <a
                href="/invoices/new"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:hidden"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New invoice
            </a>
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Outstanding</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-gray-900 tabular-nums"><?= e($formatMoney($outstandingCents)) ?></p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18v-.008zm-12 0h.008v.008H6v-.008z" />
                    </svg>
                </span>
            </div>
            <p class="mt-5 text-sm text-gray-600">
                <span class="font-semibold text-gray-900"><?= e((string) $outstandingCount) ?></span>
                <?= e($pluralize($outstandingCount, 'invoice', 'invoices')) ?> awaiting payment
            </p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Paid This Month</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-gray-900 tabular-nums"><?= e($formatMoney($paidThisMonthCents)) ?></p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-5 text-sm text-gray-600">
                <span class="font-semibold text-gray-900"><?= e((string) $paidThisMonthCount) ?></span>
                <?= e($pluralize($paidThisMonthCount, 'invoice', 'invoices')) ?> paid
            </p>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Overdue</p>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-gray-900 tabular-nums"><?= e($formatMoney($overdueCents)) ?></p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-red-600 ring-1 ring-inset ring-red-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <p class="mt-5 text-sm text-gray-600">
                <span class="font-semibold text-gray-900"><?= e((string) $overdueCount) ?></span>
                <?= e($pluralize($overdueCount, 'invoice', 'invoices')) ?> past due
            </p>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Quick actions</h2>
                    <p class="mt-1 text-sm text-gray-500">Jump straight into the most common billing tasks.</p>
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <a href="/invoices/new" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/70 p-4 transition hover:border-indigo-200 hover:bg-indigo-50">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm ring-1 ring-inset ring-gray-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900">Create invoice</p>
                        <p class="mt-0.5 text-xs text-gray-500">Start a new draft</p>
                    </div>
                    <svg class="h-4 w-4 text-gray-400 transition group-hover:text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="/clients/new" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/70 p-4 transition hover:border-indigo-200 hover:bg-indigo-50">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm ring-1 ring-inset ring-gray-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900">Add client</p>
                        <p class="mt-0.5 text-xs text-gray-500">Expand your customer list</p>
                    </div>
                    <svg class="h-4 w-4 text-gray-400 transition group-hover:text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="/invoices" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/70 p-4 transition hover:border-indigo-200 hover:bg-indigo-50">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm ring-1 ring-inset ring-gray-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 10.5h7.5M8.25 14.25h4.5M6 3.75h12A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75z" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900">Review invoices</p>
                        <p class="mt-0.5 text-xs text-gray-500">Manage drafts and payments</p>
                    </div>
                    <svg class="h-4 w-4 text-gray-400 transition group-hover:text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="/settings" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/70 p-4 transition hover:border-indigo-200 hover:bg-indigo-50">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm ring-1 ring-inset ring-gray-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900">Business settings</p>
                        <p class="mt-0.5 text-xs text-gray-500">Update your invoice profile</p>
                    </div>
                    <svg class="h-4 w-4 text-gray-400 transition group-hover:text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">About these numbers</h2>
                    <p class="text-xs text-gray-500">How totals are calculated</p>
                </div>
            </div>

            <dl class="mt-5 space-y-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-900">Outstanding</dt>
                    <dd class="mt-0.5 text-gray-500">Sent and overdue invoices that have not been paid.</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-900">Paid this month</dt>
                    <dd class="mt-0.5 text-gray-500">Payments recorded during the current UTC month.</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-900">Overdue</dt>
                    <dd class="mt-0.5 text-gray-500">Unpaid invoices whose due date has passed.</dd>
                </div>
                <div class="border-t border-gray-100 pt-4">
                    <dt class="font-medium text-gray-900">Currency</dt>
                    <dd class="mt-0.5 text-gray-500">
                        Totals use your default business currency (<?= e($currency) ?>). The CSV export keeps each invoice's original currency.
                    </dd>
                </div>
            </dl>
        </section>
    </div>
</div>
