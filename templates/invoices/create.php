<?php

declare(strict_types=1);

$clients = $clients ?? [];
$profile = $profile ?? [];
$form = $form ?? [];
$items = $items ?? [];
$errors = $errors ?? [];
$itemErrors = $itemErrors ?? [];

if ($items === []) {
    $items = [['description' => '', 'quantity' => '1', 'unit_price' => '']];
}

$inputClass = 'mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500';
$currency = (string) ($form['currency'] ?? 'USD');
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">Invoice builder</p>
            <p class="mt-1 text-sm text-gray-600">Build a draft invoice with line items, discounts, and tax.</p>
        </div>
        <a href="/invoices" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 transition hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to invoices
        </a>
    </div>

    <?php if ($clients === []): ?>
        <div class="flex flex-col items-center rounded-xl border border-gray-200 bg-white px-6 py-16 text-center shadow-sm">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
            </span>
            <h2 class="mt-5 text-lg font-semibold text-gray-900">Add a client first</h2>
            <p class="mt-2 max-w-md text-sm text-gray-500">Invoices need a client. Create one before building an invoice.</p>
            <a href="/clients/new" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add client
            </a>
        </div>
    <?php else: ?>
        <form id="invoice-form" method="post" action="/invoices" data-currency="<?= e($currency) ?>" class="space-y-6">
            <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">

            <?php if (isset($errors['general'])): ?>
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <?= e($errors['general']) ?>
                </div>
            <?php endif; ?>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">Invoice details</h2>
                <p class="mt-1 text-sm text-gray-500">
                    The invoice number is generated automatically using the <?= e((string) ($profile['invoice_prefix'] ?? 'INV')) ?> prefix.
                </p>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="client_id" class="block text-sm font-semibold text-gray-700">Client</label>
                        <select
                            id="client_id"
                            name="client_id"
                            required
                            class="<?= e($inputClass) ?>"
                        >
                            <option value="">Select a client</option>
                            <?php foreach ($clients as $client): ?>
                                <option
                                    value="<?= e($client['id']) ?>"
                                    <?= (int) ($form['client_id'] ?? 0) === (int) $client['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($client['name']) ?><?= !empty($client['company']) ? ' - ' . e($client['company']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['client_id'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= e($errors['client_id']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="issue_date" class="block text-sm font-semibold text-gray-700">Issue date</label>
                        <input
                            id="issue_date"
                            name="issue_date"
                            type="date"
                            required
                            value="<?= e($form['issue_date'] ?? '') ?>"
                            class="<?= e($inputClass) ?>"
                        >
                        <?php if (isset($errors['issue_date'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= e($errors['issue_date']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="due_date" class="block text-sm font-semibold text-gray-700">Due date</label>
                        <input
                            id="due_date"
                            name="due_date"
                            type="date"
                            required
                            value="<?= e($form['due_date'] ?? '') ?>"
                            class="<?= e($inputClass) ?>"
                        >
                        <?php if (isset($errors['due_date'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= e($errors['due_date']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-semibold text-gray-700">Currency</label>
                        <input
                            id="currency"
                            name="currency"
                            type="text"
                            maxlength="3"
                            required
                            value="<?= e($currency) ?>"
                            class="<?= e($inputClass) ?> uppercase"
                        >
                        <?php if (isset($errors['currency'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= e($errors['currency']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Line items</h2>
                        <p class="mt-1 text-sm text-gray-500">Quantities and prices accept up to two decimal places.</p>
                    </div>
                    <button
                        id="add-line-item"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add line item
                    </button>
                </div>

                <?php if (isset($itemErrors['general'])): ?>
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <?= e($itemErrors['general']) ?>
                    </div>
                <?php endif; ?>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Description</th>
                                <th class="w-32 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Qty</th>
                                <th class="w-40 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Unit price</th>
                                <th class="w-32 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Line total</th>
                                <th class="w-24 px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody id="line-items" class="divide-y divide-gray-100">
                            <?php foreach ($items as $index => $item): ?>
                                <?php $rowErrors = $itemErrors[$index] ?? []; ?>
                                <tr data-item-row class="transition-colors hover:bg-gray-50/70">
                                    <td class="px-4 py-3 align-top">
                                        <input
                                            type="text"
                                            name="item_description[]"
                                            data-item-description
                                            value="<?= e($item['description'] ?? '') ?>"
                                            placeholder="Service or product"
                                            class="<?= e($inputClass) ?>"
                                        >
                                        <?php if (isset($rowErrors['description'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?= e($rowErrors['description']) ?></p>
                                        <?php endif; ?>
                                        <?php if (isset($rowErrors['general'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?= e($rowErrors['general']) ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <input
                                            type="number"
                                            name="item_quantity[]"
                                            data-item-quantity
                                            min="0.01"
                                            step="0.01"
                                            value="<?= e($item['quantity'] ?? '') ?>"
                                            class="<?= e($inputClass) ?> text-right"
                                        >
                                        <?php if (isset($rowErrors['quantity'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?= e($rowErrors['quantity']) ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <input
                                            type="number"
                                            name="item_unit_price[]"
                                            data-item-price
                                            min="0"
                                            step="0.01"
                                            value="<?= e($item['unit_price'] ?? '') ?>"
                                            placeholder="0.00"
                                            class="<?= e($inputClass) ?> text-right"
                                        >
                                        <?php if (isset($rowErrors['unit_price'])): ?>
                                            <p class="mt-1 text-sm text-red-600"><?= e($rowErrors['unit_price']) ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right align-top">
                                        <span data-line-total class="text-sm font-medium text-gray-700">0.00</span>
                                    </td>
                                    <td class="px-4 py-3 text-right align-top">
                                        <button
                                            type="button"
                                            data-remove-item
                                            class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label for="notes" class="block text-sm font-semibold text-gray-700">Notes</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="6"
                            class="<?= e($inputClass) ?>"
                            placeholder="Optional notes for this invoice"
                        ><?= e($form['notes'] ?? '') ?></textarea>

                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="discount" class="block text-sm font-semibold text-gray-700">Discount (%)</label>
                                <input
                                    id="discount"
                                    name="discount"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    value="<?= e($form['discount'] ?? '0.00') ?>"
                                    class="<?= e($inputClass) ?> text-right"
                                >
                                <?php if (isset($errors['discount'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= e($errors['discount']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="tax-rate" class="block text-sm font-semibold text-gray-700">Tax rate (%)</label>
                                <input
                                    id="tax-rate"
                                    name="tax_rate"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    value="<?= e($form['tax_rate'] ?? '0.00') ?>"
                                    class="<?= e($inputClass) ?> text-right"
                                >
                                <?php if (isset($errors['tax_rate'])): ?>
                                    <p class="mt-1 text-sm text-red-600"><?= e($errors['tax_rate']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Summary</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-600">Subtotal</dt>
                                <dd id="summary-subtotal" class="font-medium text-gray-900">USD 0.00</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-600">Discount</dt>
                                <dd id="summary-discount" class="font-medium text-gray-900">USD 0.00</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-600">Tax</dt>
                                <dd id="summary-tax" class="font-medium text-gray-900">USD 0.00</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-3">
                                <dt class="text-base font-semibold text-gray-900">Total</dt>
                                <dd id="summary-total" class="text-base font-bold text-gray-900">USD 0.00</dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs text-gray-500">Final totals are recalculated on the server when you save.</p>
                    </div>
                </div>
            </section>

            <div class="sticky bottom-0 z-20 -mx-4 border-t border-gray-200 bg-white/95 px-4 py-3 shadow-[0_-1px_3px_rgba(0,0,0,0.05)] backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="/invoices" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Save draft invoice
                    </button>
                </div>
            </div>
        </form>

        <template id="line-item-template">
            <tr data-item-row class="transition-colors hover:bg-gray-50/70">
                <td class="px-4 py-3 align-top">
                    <input
                        type="text"
                        name="item_description[]"
                        data-item-description
                        placeholder="Service or product"
                        class="<?= e($inputClass) ?>"
                    >
                </td>
                <td class="px-4 py-3 align-top">
                    <input
                        type="number"
                        name="item_quantity[]"
                        data-item-quantity
                        min="0.01"
                        step="0.01"
                        value="1"
                        class="<?= e($inputClass) ?> text-right"
                    >
                </td>
                <td class="px-4 py-3 align-top">
                    <input
                        type="number"
                        name="item_unit_price[]"
                        data-item-price
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                        class="<?= e($inputClass) ?> text-right"
                    >
                </td>
                <td class="px-4 py-3 text-right align-top">
                    <span data-line-total class="text-sm font-medium text-gray-700">0.00</span>
                </td>
                <td class="px-4 py-3 text-right align-top">
                    <button
                        type="button"
                        data-remove-item
                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700"
                    >
                        Remove
                    </button>
                </td>
            </tr>
        </template>

        <script>
            (function () {
                const form = document.getElementById('invoice-form');
                const itemsBody = document.getElementById('line-items');
                const template = document.getElementById('line-item-template');
                const addButton = document.getElementById('add-line-item');

                if (!form || !itemsBody || !template || !addButton) {
                    return;
                }

                const currencyInput = document.getElementById('currency');
                const discountInput = document.getElementById('discount');
                const taxInput = document.getElementById('tax-rate');
                const subtotalOutput = document.getElementById('summary-subtotal');
                const discountOutput = document.getElementById('summary-discount');
                const taxOutput = document.getElementById('summary-tax');
                const totalOutput = document.getElementById('summary-total');
                const numberFormatter = new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                function parseScaled(value) {
                    const normalized = String(value).trim();

                    if (!/^\d+(\.\d{1,2})?$/.test(normalized)) {
                        return 0;
                    }

                    const parts = normalized.split('.');
                    const whole = parseInt(parts[0] || '0', 10);
                    const fraction = ((parts[1] || '') + '00').slice(0, 2);

                    return (whole * 100) + parseInt(fraction, 10);
                }

                function divideHalfUp(numerator, denominator) {
                    return Math.floor((numerator + Math.floor(denominator / 2)) / denominator);
                }

                function money(cents) {
                    const currency = (currencyInput.value || 'USD').toUpperCase();

                    return currency + ' ' + numberFormatter.format(cents / 100);
                }

                function updateTotals() {
                    let subtotalCents = 0;

                    itemsBody.querySelectorAll('[data-item-row]').forEach(function (row) {
                        const quantity = parseScaled(row.querySelector('[data-item-quantity]').value);
                        const unitPrice = parseScaled(row.querySelector('[data-item-price]').value);
                        const lineTotal = quantity > 0 ? divideHalfUp(quantity * unitPrice, 100) : 0;

                        subtotalCents += lineTotal;
                        row.querySelector('[data-line-total]').textContent = numberFormatter.format(lineTotal / 100);
                    });

                    const discountBasisPoints = Math.min(parseScaled(discountInput.value), 10000);
                    const taxBasisPoints = Math.min(parseScaled(taxInput.value), 10000);
                    const discountCents = divideHalfUp(subtotalCents * discountBasisPoints, 10000);
                    const discountedSubtotal = subtotalCents - discountCents;
                    const taxCents = divideHalfUp(discountedSubtotal * taxBasisPoints, 10000);
                    const totalCents = discountedSubtotal + taxCents;

                    subtotalOutput.textContent = money(subtotalCents);
                    discountOutput.textContent = '-' + money(discountCents);
                    taxOutput.textContent = money(taxCents);
                    totalOutput.textContent = money(totalCents);
                }

                addButton.addEventListener('click', function () {
                    itemsBody.appendChild(template.content.cloneNode(true));
                    updateTotals();
                });

                itemsBody.addEventListener('click', function (event) {
                    const removeButton = event.target.closest('[data-remove-item]');

                    if (!removeButton) {
                        return;
                    }

                    const row = removeButton.closest('[data-item-row]');

                    if (!row) {
                        return;
                    }

                    if (itemsBody.querySelectorAll('[data-item-row]').length <= 1) {
                        row.querySelectorAll('input').forEach(function (input) {
                            input.value = input.hasAttribute('data-item-quantity') ? '1' : '';
                        });
                    } else {
                        row.remove();
                    }

                    updateTotals();
                });

                form.addEventListener('input', updateTotals);
                updateTotals();
            })();
        </script>
    <?php endif; ?>
</div>
