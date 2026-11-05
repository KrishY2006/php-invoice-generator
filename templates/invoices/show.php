<?php

declare(strict_types=1);

$invoice = $invoice ?? [];
$items = $items ?? [];
$business = is_array($invoice['business'] ?? null) ? $invoice['business'] : [];
$client = is_array($invoice['client'] ?? null) ? $invoice['client'] : [];
$actualStatus = (string) ($invoice['status'] ?? 'draft');
$displayStatus = (string) ($invoice['display_status'] ?? $actualStatus);
$currency = strtoupper((string) ($invoice['currency'] ?? 'USD'));
$invoiceId = (string) ($invoice['id'] ?? '');

$statusLabels = [
    'draft' => 'Draft',
    'sent' => 'Sent',
    'paid' => 'Paid',
    'overdue' => 'Overdue',
    'cancelled' => 'Void',
];

$statusClasses = [
    'draft' => 'bg-slate-100 text-slate-700',
    'sent' => 'bg-sky-100 text-sky-700',
    'paid' => 'bg-emerald-100 text-emerald-700',
    'overdue' => 'bg-amber-100 text-amber-800',
    'cancelled' => 'bg-red-100 text-red-700',
];

$formatDate = static function (mixed $value): string {
    $date = is_string($value) ? strtotime($value) : false;

    return $date === false ? '' : date('M j, Y', $date);
};

$formatMoney = static function (mixed $cents) use ($currency): string {
    return $currency . ' ' . number_format(((int) $cents) / 100, 2);
};

$formatQuantity = static function (mixed $hundredths, mixed $raw = null): string {
    if (is_numeric($hundredths)) {
        $value = ((int) $hundredths) / 100;
    } elseif (is_numeric($raw)) {
        $value = (float) $raw;
    } else {
        $value = 0.0;
    }

    $formatted = number_format($value, 2, '.', '');
    $trimmed = rtrim(rtrim($formatted, '0'), '.');

    return $trimmed === '' ? '0' : $trimmed;
};

$canSend = $actualStatus === 'draft';
$canPay = in_array($actualStatus, ['sent', 'overdue'], true);
$canVoid = in_array($actualStatus, ['draft', 'sent', 'overdue'], true);
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="/invoices" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to invoices</a>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900"><?= e((string) ($invoice['invoice_number'] ?? 'Invoice')) ?></h1>
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?= e($statusClasses[$displayStatus] ?? 'bg-slate-100 text-slate-700') ?>">
                    <?= e($statusLabels[$displayStatus] ?? ucfirst($displayStatus)) ?>
                </span>
            </div>
            <p class="mt-1 text-slate-600">
                Issued <?= e($formatDate($invoice['issue_date'] ?? null)) ?>
                &middot; Due <?= e($formatDate($invoice['due_date'] ?? null)) ?>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="/invoices/<?= e($invoiceId) ?>/pdf"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Download PDF
            </a>

            <?php if ($canSend): ?>
                <form method="post" action="/invoices/<?= e($invoiceId) ?>/send">
                    <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
                    <button
                        type="submit"
                        class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700"
                    >
                        Send
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($canPay): ?>
                <form method="post" action="/invoices/<?= e($invoiceId) ?>/pay">
                    <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
                    <button
                        type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Pay
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($canVoid): ?>
                <form
                    method="post"
                    action="/invoices/<?= e($invoiceId) ?>/void"
                    onsubmit="return confirm('Void this invoice? This cannot be undone.');"
                >
                    <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
                    <button
                        type="submit"
                        class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                    >
                        Void
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($actualStatus === 'cancelled'): ?>
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="status">
            This invoice has been voided and is read-only.
        </div>
    <?php elseif ($actualStatus === 'paid'): ?>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
            Paid<?= !empty($invoice['paid_at']) ? ' on ' . e($formatDate($invoice['paid_at'])) : '' ?>.
        </div>
    <?php elseif ($displayStatus === 'overdue'): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="status">
            This invoice is overdue.
        </div>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">From</h2>
            <p class="mt-3 text-base font-semibold text-slate-900"><?= e((string) ($business['name'] ?? 'Your business')) ?></p>
            <?php if (!empty($business['address'])): ?>
                <p class="mt-1 whitespace-pre-line text-sm text-slate-600"><?= e((string) $business['address']) ?></p>
            <?php endif; ?>
            <?php if (!empty($business['email'])): ?>
                <p class="mt-1 text-sm text-slate-600"><?= e((string) $business['email']) ?></p>
            <?php endif; ?>
            <?php if (!empty($business['phone'])): ?>
                <p class="mt-1 text-sm text-slate-600"><?= e((string) $business['phone']) ?></p>
            <?php endif; ?>
            <?php if (!empty($business['tax_id'])): ?>
                <p class="mt-1 text-sm text-slate-600">Tax ID: <?= e((string) $business['tax_id']) ?></p>
            <?php endif; ?>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Bill To</h2>
            <p class="mt-3 text-base font-semibold text-slate-900"><?= e((string) ($client['name'] ?? 'Client')) ?></p>
            <?php if (!empty($client['company'])): ?>
                <p class="mt-1 text-sm text-slate-600"><?= e((string) $client['company']) ?></p>
            <?php endif; ?>
            <?php if (!empty($client['address'])): ?>
                <p class="mt-1 whitespace-pre-line text-sm text-slate-600"><?= e((string) $client['address']) ?></p>
            <?php endif; ?>
            <?php if (!empty($client['email'])): ?>
                <p class="mt-1 text-sm text-slate-600"><?= e((string) $client['email']) ?></p>
            <?php endif; ?>
            <?php if (!empty($client['tax_id'])): ?>
                <p class="mt-1 text-sm text-slate-600">Tax ID: <?= e((string) $client['tax_id']) ?></p>
            <?php endif; ?>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Invoice Details</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-slate-500">Status</dt>
                    <dd class="text-right font-medium text-slate-900"><?= e($statusLabels[$displayStatus] ?? ucfirst($displayStatus)) ?></dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-slate-500">Currency</dt>
                    <dd class="text-right font-medium text-slate-900"><?= e($currency) ?></dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-slate-500">Issued</dt>
                    <dd class="text-right font-medium text-slate-900"><?= e($formatDate($invoice['issue_date'] ?? null)) ?></dd>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <dt class="text-slate-500">Due</dt>
                    <dd class="text-right font-medium text-slate-900"><?= e($formatDate($invoice['due_date'] ?? null)) ?></dd>
                </div>
                <?php if (!empty($invoice['paid_at'])): ?>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-500">Paid</dt>
                        <dd class="text-right font-medium text-slate-900"><?= e($formatDate($invoice['paid_at'])) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </section>
    </div>

    <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Unit price</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($items === []): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">No line items.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm text-slate-900">
                                    <p class="whitespace-pre-line font-medium"><?= e((string) ($item['description'] ?? '')) ?></p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-600">
                                    <?= e($formatQuantity($item['quantity_hundredths'] ?? null, $item['quantity'] ?? null)) ?>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-600">
                                    <?= e($formatMoney($item['unit_price_cents'] ?? 0)) ?>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-slate-900">
                                    <?= e($formatMoney($item['total_cents'] ?? 0)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-6 py-5">
            <dl class="w-full max-w-xs space-y-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-600">Subtotal</dt>
                    <dd class="font-medium text-slate-900"><?= e($formatMoney($invoice['subtotal_cents'] ?? 0)) ?></dd>
                </div>
                <?php if ((int) ($invoice['discount_cents'] ?? 0) > 0): ?>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-600">Discount</dt>
                        <dd class="font-medium text-slate-900">-<?= e($formatMoney($invoice['discount_cents'] ?? 0)) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ((int) ($invoice['tax_cents'] ?? 0) > 0): ?>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-600">Tax</dt>
                        <dd class="font-medium text-slate-900"><?= e($formatMoney($invoice['tax_cents'] ?? 0)) ?></dd>
                    </div>
                <?php endif; ?>
                <div class="flex items-center justify-between gap-4 border-t border-slate-300 pt-2">
                    <dt class="text-base font-semibold text-slate-900">Total</dt>
                    <dd class="text-base font-bold text-slate-900"><?= e($formatMoney($invoice['total_cents'] ?? 0)) ?></dd>
                </div>
            </dl>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <?php if (!empty($invoice['notes'])): ?>
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Notes</h2>
                <p class="mt-3 whitespace-pre-line text-sm text-slate-700"><?= e((string) $invoice['notes']) ?></p>
            </section>
        <?php endif; ?>

        <?php if (!empty($business['bank_details'])): ?>
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Bank Details</h2>
                <p class="mt-3 whitespace-pre-line text-sm text-slate-700"><?= e((string) $business['bank_details']) ?></p>
            </section>
        <?php endif; ?>
    </div>
</div>
