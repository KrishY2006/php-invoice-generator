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
$businessName = (string) ($business['name'] ?? 'Your business');
$businessInitial = strtoupper(mb_substr($businessName, 0, 1));
$logoUrl = !empty($business['logo_path'])
    ? '/logo?v=' . rawurlencode((string) $business['logo_path'])
    : null;
?>
<div class="space-y-6">
    <div class="sticky top-16 z-20 -mx-4 border-b border-gray-200 bg-gray-50/95 px-4 py-3 shadow-sm backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
                <a
                    href="/invoices"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900"
                    aria-label="Back to invoices"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate text-lg font-semibold tracking-tight text-gray-900">
                            <?= e((string) ($invoice['invoice_number'] ?? 'Invoice')) ?>
                        </h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset <?= e($statusClasses[$displayStatus] ?? 'bg-gray-100 text-gray-500 ring-gray-200') ?>">
                            <span class="h-1.5 w-1.5 rounded-full <?= e($statusDots[$displayStatus] ?? 'bg-gray-400') ?>"></span>
                            <?= e($statusLabels[$displayStatus] ?? ucfirst($displayStatus)) ?>
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Issued <?= e($formatDate($invoice['issue_date'] ?? null)) ?>
                        <span class="mx-1 text-gray-300">/</span>
                        Due <?= e($formatDate($invoice['due_date'] ?? null)) ?>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="/invoices/<?= e($invoiceId) ?>/pdf"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    PDF
                </a>

                <?php if ($canSend): ?>
                    <form method="post" action="/invoices/<?= e($invoiceId) ?>/send">
                        <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            Send
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($canPay): ?>
                    <form method="post" action="/invoices/<?= e($invoiceId) ?>/pay">
                        <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Mark paid
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
                            class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-3.5 py-2 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                            Void
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($actualStatus === 'cancelled'): ?>
        <div class="mx-auto flex max-w-4xl items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="status">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            This invoice has been voided and is read-only.
        </div>
    <?php elseif ($actualStatus === 'paid'): ?>
        <div class="mx-auto flex max-w-4xl items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Paid<?= !empty($invoice['paid_at']) ? ' on ' . e($formatDate($invoice['paid_at'])) : '' ?>.
        </div>
    <?php elseif ($displayStatus === 'overdue'): ?>
        <div class="mx-auto flex max-w-4xl items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="status">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            This invoice is overdue. Payment is past the due date.
        </div>
    <?php endif; ?>

    <article class="mx-auto max-w-4xl overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg">
        <div class="border-b border-gray-100 px-8 py-8 sm:px-10">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                    <?php if ($logoUrl !== null): ?>
                        <img
                            src="<?= e($logoUrl) ?>"
                            alt="<?= e($businessName) ?> logo"
                            class="h-16 w-16 rounded-xl border border-gray-200 bg-gray-50 object-contain p-2"
                        >
                    <?php else: ?>
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-slate-950 text-xl font-bold text-white shadow-sm">
                            <?= e($businessInitial) ?>
                        </span>
                    <?php endif; ?>
                    <div class="min-w-0">
                        <p class="text-lg font-bold tracking-tight text-gray-900"><?= e($businessName) ?></p>
                        <?php if (!empty($business['website'])): ?>
                            <p class="mt-1 text-sm text-gray-500"><?= e((string) $business['website']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($business['email'])): ?>
                            <p class="text-sm text-gray-500"><?= e((string) $business['email']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sm:text-right">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-gray-400">Invoice</p>
                    <p class="mt-1 text-xl font-bold tracking-tight text-gray-900"><?= e((string) ($invoice['invoice_number'] ?? '')) ?></p>
                    <p class="mt-2 text-sm text-gray-500"><?= e($currency) ?></p>
                </div>
            </div>
        </div>

        <div class="grid gap-8 border-b border-gray-100 px-8 py-8 sm:grid-cols-3 sm:px-10">
            <section>
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">From</h3>
                <p class="mt-3 text-sm font-semibold text-gray-900"><?= e($businessName) ?></p>
                <?php if (!empty($business['address'])): ?>
                    <p class="mt-1 whitespace-pre-line text-sm leading-6 text-gray-500"><?= e((string) $business['address']) ?></p>
                <?php endif; ?>
                <?php if (!empty($business['phone'])): ?>
                    <p class="mt-1 text-sm text-gray-500"><?= e((string) $business['phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($business['tax_id'])): ?>
                    <p class="mt-1 text-sm text-gray-500">Tax ID: <?= e((string) $business['tax_id']) ?></p>
                <?php endif; ?>
            </section>

            <section>
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Bill To</h3>
                <p class="mt-3 text-sm font-semibold text-gray-900"><?= e((string) ($client['name'] ?? 'Client')) ?></p>
                <?php if (!empty($client['company'])): ?>
                    <p class="mt-1 text-sm text-gray-500"><?= e((string) $client['company']) ?></p>
                <?php endif; ?>
                <?php if (!empty($client['address'])): ?>
                    <p class="mt-1 whitespace-pre-line text-sm leading-6 text-gray-500"><?= e((string) $client['address']) ?></p>
                <?php endif; ?>
                <?php if (!empty($client['email'])): ?>
                    <p class="mt-1 text-sm text-gray-500"><?= e((string) $client['email']) ?></p>
                <?php endif; ?>
                <?php if (!empty($client['tax_id'])): ?>
                    <p class="mt-1 text-sm text-gray-500">Tax ID: <?= e((string) $client['tax_id']) ?></p>
                <?php endif; ?>
            </section>

            <section>
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Invoice Details</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="text-right font-semibold text-gray-900"><?= e($statusLabels[$displayStatus] ?? ucfirst($displayStatus)) ?></dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-500">Issued</dt>
                        <dd class="text-right font-semibold text-gray-900"><?= e($formatDate($invoice['issue_date'] ?? null)) ?></dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-gray-500">Due</dt>
                        <dd class="text-right font-semibold text-gray-900"><?= e($formatDate($invoice['due_date'] ?? null)) ?></dd>
                    </div>
                    <?php if (!empty($invoice['paid_at'])): ?>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-gray-500">Paid</dt>
                            <dd class="text-right font-semibold text-gray-900"><?= e($formatDate($invoice['paid_at'])) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </section>
        </div>

        <div class="px-8 py-8 sm:px-10">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th scope="col" class="pb-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-400">Description</th>
                            <th scope="col" class="w-24 pb-3 text-right text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-400">Qty</th>
                            <th scope="col" class="w-36 pb-3 text-right text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-400">Unit price</th>
                            <th scope="col" class="w-36 pb-3 text-right text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-400">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if ($items === []): ?>
                            <tr>
                                <td colspan="4" class="py-10 text-center text-sm text-gray-500">No line items.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="py-4 pr-4 text-sm text-gray-900">
                                        <p class="whitespace-pre-line font-medium"><?= e((string) ($item['description'] ?? '')) ?></p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-600 tabular-nums">
                                        <?= e($formatQuantity($item['quantity_hundredths'] ?? null, $item['quantity'] ?? null)) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-600 tabular-nums">
                                        <?= e($formatMoney($item['unit_price_cents'] ?? 0)) ?>
                                    </td>
                                    <td class="whitespace-nowrap py-4 pl-4 text-right text-sm font-semibold text-gray-900 tabular-nums">
                                        <?= e($formatMoney($item['total_cents'] ?? 0)) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-gray-100 bg-gray-50/70 px-8 py-6 sm:px-10">
            <div class="flex justify-end">
                <dl class="w-full max-w-sm space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-6">
                        <dt class="text-gray-500">Subtotal</dt>
                        <dd class="font-semibold text-gray-900 tabular-nums"><?= e($formatMoney($invoice['subtotal_cents'] ?? 0)) ?></dd>
                    </div>
                    <?php if ((int) ($invoice['discount_cents'] ?? 0) > 0): ?>
                        <div class="flex items-center justify-between gap-6">
                            <dt class="text-gray-500">Discount</dt>
                            <dd class="font-semibold text-gray-900 tabular-nums">-<?= e($formatMoney($invoice['discount_cents'] ?? 0)) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ((int) ($invoice['tax_cents'] ?? 0) > 0): ?>
                        <div class="flex items-center justify-between gap-6">
                            <dt class="text-gray-500">Tax</dt>
                            <dd class="font-semibold text-gray-900 tabular-nums"><?= e($formatMoney($invoice['tax_cents'] ?? 0)) ?></dd>
                        </div>
                    <?php endif; ?>
                    <div class="flex items-center justify-between gap-6 border-t border-gray-300 pt-3">
                        <dt class="text-base font-semibold text-gray-900">Total</dt>
                        <dd class="text-xl font-bold tracking-tight text-gray-900 tabular-nums"><?= e($formatMoney($invoice['total_cents'] ?? 0)) ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <?php if (!empty($invoice['notes']) || !empty($business['bank_details']) || !empty($business['footer_note'])): ?>
            <div class="grid gap-8 border-t border-gray-100 px-8 py-8 sm:grid-cols-2 sm:px-10">
                <div class="space-y-6">
                    <?php if (!empty($invoice['notes'])): ?>
                        <section>
                            <h3 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Notes</h3>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600"><?= e((string) $invoice['notes']) ?></p>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($business['footer_note'])): ?>
                        <p class="text-sm italic text-gray-400"><?= e((string) $business['footer_note']) ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($business['bank_details'])): ?>
                    <section class="rounded-xl border border-gray-100 bg-gray-50 p-5">
                        <h3 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Payment details</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600"><?= e((string) $business['bank_details']) ?></p>
                    </section>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </article>
</div>
