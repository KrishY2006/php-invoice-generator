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
<div class="space-y-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
            <p class="mt-1 text-slate-600">Welcome back, <?= e($email ?? 'Admin') ?>.</p>
        </div>

        <a
            href="/dashboard/export.csv"
            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Export invoices CSV
        </a>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="rounded-2xl border border-sky-200 bg-gradient-to-br from-sky-50 to-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-700">Outstanding</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900"><?= e($formatMoney($outstandingCents)) ?></p>
            <p class="mt-2 text-sm text-slate-600">
                <?= e((string) $outstandingCount) ?>
                <?= e($pluralize($outstandingCount, 'invoice', 'invoices')) ?> awaiting payment
            </p>
        </section>

        <section class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Paid This Month</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900"><?= e($formatMoney($paidThisMonthCents)) ?></p>
            <p class="mt-2 text-sm text-slate-600">
                <?= e((string) $paidThisMonthCount) ?>
                <?= e($pluralize($paidThisMonthCount, 'invoice', 'invoices')) ?> paid
            </p>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-amber-800">Overdue</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900"><?= e($formatMoney($overdueCents)) ?></p>
            <p class="mt-2 text-sm text-slate-600">
                <?= e((string) $overdueCount) ?>
                <?= e($pluralize($overdueCount, 'invoice', 'invoices')) ?> past due
            </p>
        </section>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="/invoices" class="rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md">
            <h2 class="text-lg font-semibold text-slate-900">Invoices</h2>
            <p class="mt-2 text-sm text-slate-600">Build invoice drafts and review saved invoices.</p>
        </a>

        <a href="/clients" class="rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md">
            <h2 class="text-lg font-semibold text-slate-900">Clients</h2>
            <p class="mt-2 text-sm text-slate-600">Add and manage the clients you invoice.</p>
        </a>

        <a href="/settings" class="rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md">
            <h2 class="text-lg font-semibold text-slate-900">Business settings</h2>
            <p class="mt-2 text-sm text-slate-600">Update your business profile, logo, and invoice defaults.</p>
        </a>
    </div>

    <p class="text-xs text-slate-500">
        Dashboard totals use your default business currency (<?= e($currency) ?>). The CSV export includes the currency stored on each invoice.
    </p>
</div>
