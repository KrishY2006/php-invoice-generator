<?php

declare(strict_types=1);

$profile = $profile ?? [];
$errors = $errors ?? [];
$maxLogoKb = $maxLogoKb ?? 2048;

$inputClass = 'mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500';
$labelClass = 'block text-sm font-semibold text-gray-700';
?>
<div class="space-y-6">
    <div>
        <p class="text-sm font-medium text-gray-500">Workspace profile</p>
        <p class="mt-1 text-sm text-gray-600">This information appears on your invoices and exported documents.</p>
    </div>

    <form method="post" action="/settings" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">

        <div class="space-y-6">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-base font-semibold text-gray-900">Business details</h2>
                    <p class="mt-1 text-sm text-gray-500">Your legal and contact information.</p>
                </div>

                <div class="p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="<?= e($labelClass) ?>">Business name <span class="text-red-500">*</span></label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                required
                                value="<?= e($profile['name'] ?? '') ?>"
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
                                value="<?= e($profile['email'] ?? '') ?>"
                                class="<?= e($inputClass) ?>"
                            >
                            <?php if (isset($errors['email'])): ?>
                                <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="phone" class="<?= e($labelClass) ?>">Phone</label>
                            <input
                                id="phone"
                                name="phone"
                                type="text"
                                value="<?= e($profile['phone'] ?? '') ?>"
                                class="<?= e($inputClass) ?>"
                            >
                        </div>

                        <div>
                            <label for="website" class="<?= e($labelClass) ?>">Website</label>
                            <input
                                id="website"
                                name="website"
                                type="url"
                                placeholder="https://example.com"
                                value="<?= e($profile['website'] ?? '') ?>"
                                class="<?= e($inputClass) ?>"
                            >
                            <?php if (isset($errors['website'])): ?>
                                <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['website']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="tax_id" class="<?= e($labelClass) ?>">Tax ID</label>
                            <input
                                id="tax_id"
                                name="tax_id"
                                type="text"
                                value="<?= e($profile['tax_id'] ?? '') ?>"
                                class="<?= e($inputClass) ?>"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="<?= e($labelClass) ?>">Address</label>
                            <textarea
                                id="address"
                                name="address"
                                rows="4"
                                class="<?= e($inputClass) ?> resize-y"
                            ><?= e($profile['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-base font-semibold text-gray-900">Invoice defaults</h2>
                    <p class="mt-1 text-sm text-gray-500">Defaults used when creating a new invoice.</p>
                </div>

                <div class="p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="currency" class="<?= e($labelClass) ?>">Currency</label>
                            <input
                                id="currency"
                                name="currency"
                                type="text"
                                maxlength="3"
                                required
                                value="<?= e($profile['currency'] ?? 'USD') ?>"
                                class="<?= e($inputClass) ?> uppercase"
                            >
                            <?php if (isset($errors['currency'])): ?>
                                <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['currency']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="invoice_prefix" class="<?= e($labelClass) ?>">Invoice prefix</label>
                            <input
                                id="invoice_prefix"
                                name="invoice_prefix"
                                type="text"
                                maxlength="20"
                                value="<?= e($profile['invoice_prefix'] ?? 'INV') ?>"
                                class="<?= e($inputClass) ?>"
                            >
                            <?php if (isset($errors['invoice_prefix'])): ?>
                                <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['invoice_prefix']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="default_tax_rate" class="<?= e($labelClass) ?>">Default tax rate (%)</label>
                            <input
                                id="default_tax_rate"
                                name="default_tax_rate"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                value="<?= e($profile['default_tax_rate'] ?? '0.00') ?>"
                                class="<?= e($inputClass) ?> text-right tabular-nums"
                            >
                            <?php if (isset($errors['default_tax_rate'])): ?>
                                <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['default_tax_rate']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="payment_terms" class="<?= e($labelClass) ?>">Payment terms</label>
                            <input
                                id="payment_terms"
                                name="payment_terms"
                                type="text"
                                value="<?= e($profile['payment_terms'] ?? '') ?>"
                                class="<?= e($inputClass) ?>"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label for="bank_details" class="<?= e($labelClass) ?>">Bank details</label>
                            <textarea
                                id="bank_details"
                                name="bank_details"
                                rows="3"
                                class="<?= e($inputClass) ?> resize-y"
                            ><?= e($profile['bank_details'] ?? '') ?></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="footer_note" class="<?= e($labelClass) ?>">Invoice footer note</label>
                            <textarea
                                id="footer_note"
                                name="footer_note"
                                rows="2"
                                class="<?= e($inputClass) ?> resize-y"
                            ><?= e($profile['footer_note'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-base font-semibold text-gray-900">Logo</h2>
                    <p class="mt-1 text-sm text-gray-500">Shown on invoices and PDFs.</p>
                </div>

                <div class="p-6">
                    <?php if (!empty($profile['logo_path'])): ?>
                        <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <img
                                src="/logo?v=<?= e(rawurlencode((string) $profile['logo_path'])) ?>"
                                alt="Current business logo"
                                class="h-16 w-16 rounded-xl border border-gray-200 bg-white object-contain p-2"
                            >
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">Current logo</p>
                                <p class="mt-0.5 text-xs text-gray-500">Upload a new file to replace it.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col items-center rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-gray-400 shadow-sm ring-1 ring-inset ring-gray-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                </svg>
                            </span>
                            <p class="mt-3 text-sm font-medium text-gray-700">No logo uploaded</p>
                            <p class="mt-1 text-xs text-gray-500">Add a brand mark to your invoices.</p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-5">
                        <label for="logo" class="<?= e($labelClass) ?>">Upload logo</label>
                        <input
                            id="logo"
                            name="logo"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            class="mt-1.5 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600 shadow-sm transition file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <p class="mt-2 text-xs text-gray-500">JPEG, PNG, WebP, or GIF. Maximum <?= e($maxLogoKb) ?> KB.</p>
                        <?php if (isset($errors['logo'])): ?>
                            <p class="mt-1.5 text-sm font-medium text-red-600"><?= e($errors['logo']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm xl:sticky xl:top-24">
                <h2 class="text-base font-semibold text-gray-900">Save changes</h2>
                <p class="mt-1 text-sm text-gray-500">Update your business profile and invoice defaults.</p>

                <div class="mt-5 flex flex-col gap-3">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Save settings
                    </button>
                    <a href="/dashboard" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </section>
        </div>
    </form>
</div>
