<?php

declare(strict_types=1);

$profile = $profile ?? [];
$errors = $errors ?? [];
$maxLogoKb = $maxLogoKb ?? 2048;
?>
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Business Settings</h1>
        <p class="mt-1 text-slate-600">This information appears on your invoices.</p>
    </div>

    <form method="post" action="/settings" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Business details</h2>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-slate-700">Business name</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        value="<?= e($profile['name'] ?? '') ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                    <?php if (isset($errors['name'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="<?= e($profile['email'] ?? '') ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                    <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        value="<?= e($profile['phone'] ?? '') ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                </div>

                <div>
                    <label for="website" class="block text-sm font-medium text-slate-700">Website</label>
                    <input
                        id="website"
                        name="website"
                        type="url"
                        placeholder="https://example.com"
                        value="<?= e($profile['website'] ?? '') ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                    <?php if (isset($errors['website'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['website']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="tax_id" class="block text-sm font-medium text-slate-700">Tax ID</label>
                    <input
                        id="tax_id"
                        name="tax_id"
                        type="text"
                        value="<?= e($profile['tax_id'] ?? '') ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    ><?= e($profile['address'] ?? '') ?></textarea>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Invoice defaults</h2>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="currency" class="block text-sm font-medium text-slate-700">Currency</label>
                    <input
                        id="currency"
                        name="currency"
                        type="text"
                        maxlength="3"
                        required
                        value="<?= e($profile['currency'] ?? 'USD') ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 uppercase text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                    <?php if (isset($errors['currency'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['currency']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="invoice_prefix" class="block text-sm font-medium text-slate-700">Invoice prefix</label>
                    <input
                        id="invoice_prefix"
                        name="invoice_prefix"
                        type="text"
                        maxlength="20"
                        value="<?= e($profile['invoice_prefix'] ?? 'INV') ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                    <?php if (isset($errors['invoice_prefix'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['invoice_prefix']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="default_tax_rate" class="block text-sm font-medium text-slate-700">Default tax rate (%)</label>
                    <input
                        id="default_tax_rate"
                        name="default_tax_rate"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        value="<?= e($profile['default_tax_rate'] ?? '0.00') ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                    <?php if (isset($errors['default_tax_rate'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['default_tax_rate']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="payment_terms" class="block text-sm font-medium text-slate-700">Payment terms</label>
                    <input
                        id="payment_terms"
                        name="payment_terms"
                        type="text"
                        value="<?= e($profile['payment_terms'] ?? '') ?>"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="bank_details" class="block text-sm font-medium text-slate-700">Bank details</label>
                    <textarea
                        id="bank_details"
                        name="bank_details"
                        rows="3"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    ><?= e($profile['bank_details'] ?? '') ?></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="footer_note" class="block text-sm font-medium text-slate-700">Invoice footer note</label>
                    <textarea
                        id="footer_note"
                        name="footer_note"
                        rows="2"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    ><?= e($profile['footer_note'] ?? '') ?></textarea>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Logo</h2>

            <?php if (!empty($profile['logo_path'])): ?>
                <div class="mt-5 flex items-center gap-4">
                    <img
                        src="/logo?v=<?= e(rawurlencode((string) $profile['logo_path'])) ?>"
                        alt="Current business logo"
                        class="h-20 w-20 rounded-lg border border-slate-200 bg-slate-50 object-contain p-2"
                    >
                    <p class="text-sm text-slate-500">Current logo. Upload a new file to replace it.</p>
                </div>
            <?php endif; ?>

            <div class="mt-5">
                <label for="logo" class="block text-sm font-medium text-slate-700">Upload logo</label>
                <input
                    id="logo"
                    name="logo"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-slate-700"
                >
                <p class="mt-1 text-sm text-slate-500">JPEG, PNG, WebP, or GIF. Maximum <?= e($maxLogoKb) ?> KB.</p>
                <?php if (isset($errors['logo'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['logo']) ?></p>
                <?php endif; ?>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="/dashboard" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
            <button
                type="submit"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
            >
                Save settings
            </button>
        </div>
    </form>
</div>
