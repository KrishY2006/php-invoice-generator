<?php

declare(strict_types=1);

$invoices = $invoices ?? [];

$statusClasses = [
    'draft' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'sent' => 'bg-blue-50 text-blue-700 ring-blue-200',
    'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'overdue' => 'bg-red-50 text-red-700 ring-red-200',
    'cancelled' => 'bg-gray-100 text-gray-500 ring-gray-200',
];

$statusDots = [
    'draft' => 'bg-amber-500',
    'sent' => 'bg-blue-500',
    'paid' => 'bg-emerald-500',
    'overdue' => 'bg-red-500',
    'cancelled' => 'bg-gray-400',
];

$formatDate = static function (mixed $value): string {
    $date = is_string($value) ? strtotime($value) : false;

    return $date === false ? '' : date('M j, Y', $date);
};
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">Billing ledger</p>
            <p class="mt-1 text-sm text-gray-600">Create, track, and manage every invoice from one place.</p>
        </div>
        <a
            href="/invoices/new"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New invoice
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <?php if ($invoices === []): ?>
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 10.5h7.5M8.25 14.25h4.5M6 3.75h12A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75z" />
                    </svg>
                </span>
                <h2 class="mt-5 text-lg font-semibold text-gray-900">No invoices yet</h2>
                <p class="mt-2 max-w-md text-sm text-gray-500">Create your first invoice and save it as a draft.</p>
                <a href="/invoices/new" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Create invoice
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Invoice</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Client</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Issued</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Due</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Total</th>
                            <th scope="col" class="w-16 px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($invoices as $invoice): ?>
                            <?php
                            $status = (string) ($invoice['status'] ?? 'draft');
                            $dueDate = (string) ($invoice['due_date'] ?? '');

                            if ($status === 'sent' && $dueDate !== '' && $dueDate < date('Y-m-d')) {
                                $status = 'overdue';
                            }

                            $statusLabel = $status === 'cancelled' ? 'Void' : ucfirst($status);
                            $badgeClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-500 ring-gray-200';
                            $dotClass = $statusDots[$status] ?? 'bg-gray-400';
                            $currency = (string) ($invoice['currency'] ?? 'USD');
                            $totalCents = (int) ($invoice['total_cents'] ?? 0);
                            ?>
                            <tr class="group transition-colors hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <a href="/invoices/<?= e($invoice['id'] ?? '') ?>" class="font-semibold text-gray-900 transition hover:text-indigo-600">
                                        <?= e($invoice['invoice_number'] ?? '') ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900"><?= e($invoice['client_name'] ?? '') ?></p>
                                    <?php if (!empty($invoice['client_company'])): ?>
                                        <p class="mt-0.5 text-sm text-gray-500"><?= e($invoice['client_company']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                    <?= e($formatDate($invoice['issue_date'] ?? null)) ?>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                    <?= e($formatDate($invoice['due_date'] ?? null)) ?>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset <?= e($badgeClass) ?>">
                                        <span class="h-1.5 w-1.5 rounded-full <?= e($dotClass) ?>"></span>
                                        <?= e($statusLabel) ?>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900 tabular-nums">
                                    <?= e($currency . ' ' . number_format($totalCents / 100, 2)) ?>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a
                                        href="/invoices/<?= e($invoice['id'] ?? '') ?>"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-400 shadow-sm transition group-hover:border-indigo-200 group-hover:text-indigo-600"
                                        aria-label="View invoice <?= e($invoice['invoice_number'] ?? '') ?>"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 bg-gray-50/60 px-6 py-3 text-xs text-gray-500">
                <?= e((string) count($invoices)) ?> <?= count($invoices) === 1 ? 'invoice' : 'invoices' ?> total
            </div>
        <?php endif; ?>
    </div>
</div>
