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

$inputClass = 'mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200';
$currency = (string) ($form['currency'] ?? 'USD');
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">New Invoice</h1>
            <p class="mt-1 text-slate-600">Build a draft invoice with line items, discounts, and tax.</p>
        </div>
        <a href="/invoices" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to invoices</a>
    </div>

    <?php if ($clients === []): ?>
        <div class="rounded-2xl bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Add a client first</h2>
            <p class="mt-2 text-sm text-slate-500">Invoices need a client. Create one before building an invoice.</p>
            <a href="/clients/new" class="mt-5 inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                Add client
            </a>
        </div>
    <?php else: ?>
        <form id="invoice-form" method="post" action="/invoices" data-currency="<?= e($currency) ?>" class="space-y-6">
            <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">

            <?php if (isset($errors['general'])): ?>
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <?= e($errors['general']) ?>
                </div>
            <?php endif; ?>

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Invoice details</h2>
                <p class="mt-1 text-sm text-slate-500">
                    The invoice number is generated automatically using the <?= e((string) ($profile['invoice_prefix'] ?? 'INV')) ?> prefix.
                </p>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="client_id" class="block text-sm font-medium text-slate-700">Client</label>
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
                        <label for="issue_date" class="block text-sm font-medium text-slate-700">Issue date</label>
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
                        <label for="due_date" class="block text-sm font-medium text-slate-700">Due date</label>
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
                        <label for="currency" class="block text-sm font-medium text-slate-700">Currency</label>
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

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Line items</h2>
                        <p class="mt-1 text-sm text-slate-500">Quantities and prices accept up to two decimal places.</p>
                    </div>
                    <button
                        id="add-line-item"
                        type="button"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Add line item
                    </button>
                </div>

                <?php if (isset($itemErrors['general'])): ?>
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <?= e($itemErrors['general']) ?>
                    </div>
                <?php endif; ?>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Description</th>
                                <th class="w-32 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
                                <th class="w-40 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Unit price</th>
                                <th class="w-32 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Line total</th>
                                <th class="w-24 px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody id="line-items" class="divide-y divide-slate-100">
                            <?php foreach ($items as $index => $item): ?>
                                <?php $rowErrors = $itemErrors[$index] ?? []; ?>
                                <tr data-item-row>
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
                                        <span data-line-total class="text-sm font-medium text-slate-700">0.00</span>
                                    </td>
                                    <td class="px-4 py-3 text-right align-top">
                                        <button
                                            type="button"
                                            data-remove-item
                                            class="text-sm font-medium text-red-600 hover:text-red-800"
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

            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="6"
                            class="<?= e($inputClass) ?>"
                            placeholder="Optional notes for this invoice"
                        ><?= e($form['notes'] ?? '') ?></textarea>

                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="discount" class="block text-sm font-medium text-slate-700">Discount (%)</label>
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
                                <label for="tax-rate" class="block text-sm font-medium text-slate-700">Tax rate (%)</label>
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

                    <div class="rounded-xl bg-slate-50 p-5">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Summary</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-600">Subtotal</dt>
                                <dd id="summary-subtotal" class="font-medium text-slate-900">USD 0.00</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-600">Discount</dt>
                                <dd id="summary-discount" class="font-medium text-slate-900">USD 0.00</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-600">Tax</dt>
                                <dd id="summary-tax" class="font-medium text-slate-900">USD 0.00</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-3">
                                <dt class="text-base font-semibold text-slate-900">Total</dt>
                                <dd id="summary-total" class="text-base font-bold text-slate-900">USD 0.00</dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs text-slate-500">Final totals are recalculated on the server when you save.</p>
                    </div>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <a href="/invoices" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                >
                    Save draft invoice
                </button>
            </div>
        </form>

        <template id="line-item-template">
            <tr data-item-row>
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
                    <span data-line-total class="text-sm font-medium text-slate-700">0.00</span>
                </td>
                <td class="px-4 py-3 text-right align-top">
                    <button
                        type="button"
                        data-remove-item
                        class="text-sm font-medium text-red-600 hover:text-red-800"
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
