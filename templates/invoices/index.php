<?php

declare(strict_types=1);

$invoices = $invoices ?? [];

$statusClasses = [
    'draft' => 'bg-slate-100 text-slate-700',
    'sent' => 'bg-sky-100 text-sky-700',
    'paid' => 'bg-emerald-100 text-emerald-700',
    'overdue' => 'bg-amber-100 text-amber-800',
    'cancelled' => 'bg-red-100 text-red-700',
];
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Invoices</h1>
            <p class="mt-1 text-slate-600">Create and track invoices for your clients.</p>
        </div>
        <a
            href="/invoices/new"
            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
        >
            New invoice
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <?php if ($invoices === []): ?>
            <div class="px-6 py-16 text-center">
                <h2 class="text-lg font-semibold text-slate-900">No invoices yet</h2>
                <p class="mt-2 text-sm text-slate-500">Create your first invoice and save it as a draft.</p>
                <a href="/invoices/new" class="mt-5 inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    Create invoice
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Issued</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Due</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($invoices as $invoice): ?>
                            <?php
                            $status = (string) ($invoice['status'] ?? 'draft');
                            $dueDate = (string) ($invoice['due_date'] ?? '');

                            if ($status === 'sent' && $dueDate !== '' && $dueDate < date('Y-m-d')) {
                                $status = 'overdue';
                            }

                            $statusLabel = $status === 'cancelled' ? 'Void' : ucfirst($status);
                            $badgeClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700';
                            $currency = (string) ($invoice['currency'] ?? 'USD');
                            $totalCents = (int) ($invoice['total_cents'] ?? 0);
                            ?>
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">
                                    <a href="/invoices/<?= e($invoice['id'] ?? '') ?>" class="hover:text-slate-600">
                                        <?= e($invoice['invoice_number'] ?? '') ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-slate-900"><?= e($invoice['client_name'] ?? '') ?></p>
                                    <?php if (!empty($invoice['client_company'])): ?>
                                        <p class="mt-0.5 text-sm text-slate-500"><?= e($invoice['client_company']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                    <?= e(date('M j, Y', strtotime((string) ($invoice['issue_date'] ?? '')))) ?>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                    <?= e(date('M j, Y', strtotime((string) ($invoice['due_date'] ?? '')))) ?>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= e($badgeClass) ?>">
                                        <?= e($statusLabel) ?>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-slate-900">
                                    <?= e($currency . ' ' . number_format($totalCents / 100, 2)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
