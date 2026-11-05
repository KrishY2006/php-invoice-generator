<?php

declare(strict_types=1);

$client = $client ?? [];
$errors = $errors ?? [];

$inputClass = 'mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500';
$labelClass = 'block text-sm font-semibold text-gray-700';
?>
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">Client directory</p>
            <p class="mt-1 text-sm text-gray-600">Add a client to your invoice list.</p>
        </div>
        <a href="/clients" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 transition hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to clients
        </a>
    </div>

    <form method="post" action="/clients" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">

        <div class="border-b border-gray-100 px-6 py-5">
            <h2 class="text-base font-semibold text-gray-900">Contact details</h2>
            <p class="mt-1 text-sm text-gray-500">The information below will appear on invoices for this client.</p>
        </div>

        <div class="p-6">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="<?= e($labelClass) ?>">Client name <span class="text-red-500">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        autofocus
                        value="<?= e($client['name'] ?? '') ?>"
                        class="<?= e($inputClass) ?>"
                    >
                    <?php if (isset($errors['name'])): ?>
                        <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email" class="<?= e($labelClass) ?>">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="<?= e($client['email'] ?? '') ?>"
                        class="<?= e($inputClass) ?>"
                    >
                    <?php if (isset($errors['email'])): ?>
                        <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="company" class="<?= e($labelClass) ?>">Company</label>
                    <input
                        id="company"
                        name="company"
                        type="text"
                        value="<?= e($client['company'] ?? '') ?>"
                        class="<?= e($inputClass) ?>"
                    >
                    <?php if (isset($errors['company'])): ?>
                        <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['company']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="sm:col-span-2">
                    <label for="tax_id" class="<?= e($labelClass) ?>">Tax ID</label>
                    <input
                        id="tax_id"
                        name="tax_id"
                        type="text"
                        value="<?= e($client['tax_id'] ?? '') ?>"
                        class="<?= e($inputClass) ?>"
                    >
                    <?php if (isset($errors['tax_id'])): ?>
                        <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['tax_id']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="<?= e($labelClass) ?>">Address</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="4"
                        class="<?= e($inputClass) ?> resize-y"
                    ><?= e($client['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4">
            <a href="/clients" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50">
                Cancel
            </a>
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create client
            </button>
        </div>
    </form>
</div>
