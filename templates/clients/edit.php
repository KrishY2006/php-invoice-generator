<?php

declare(strict_types=1);

$client = $client ?? [];
$errors = $errors ?? [];
$clientId = (int) ($client['id'] ?? 0);
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Client</h1>
            <p class="mt-1 text-slate-600">Update this client's details.</p>
        </div>
        <a href="/clients" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to clients</a>
    </div>

    <form method="post" action="/clients/<?= e($clientId) ?>/edit" class="rounded-2xl bg-white p-6 shadow-sm">
        <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-slate-700">Client name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    value="<?= e($client['name'] ?? '') ?>"
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
                    value="<?= e($client['email'] ?? '') ?>"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                >
                <?php if (isset($errors['email'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['email']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="company" class="block text-sm font-medium text-slate-700">Company</label>
                <input
                    id="company"
                    name="company"
                    type="text"
                    value="<?= e($client['company'] ?? '') ?>"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                >
                <?php if (isset($errors['company'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['company']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="tax_id" class="block text-sm font-medium text-slate-700">Tax ID</label>
                <input
                    id="tax_id"
                    name="tax_id"
                    type="text"
                    value="<?= e($client['tax_id'] ?? '') ?>"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                >
                <?php if (isset($errors['tax_id'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['tax_id']) ?></p>
                <?php endif; ?>
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"
                ><?= e($client['address'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between gap-3">
            <a href="/clients" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
            <button
                type="submit"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
            >
                Save changes
            </button>
        </div>
    </form>

    <form
        method="post"
        action="/clients/<?= e($clientId) ?>/delete"
        onsubmit="return confirm('Delete this client? This cannot be undone.');"
        class="rounded-2xl border border-red-200 bg-red-50 p-6"
    >
        <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
        <h2 class="text-lg font-semibold text-red-800">Delete client</h2>
        <p class="mt-1 text-sm text-red-700">Clients with invoices cannot be deleted.</p>
        <button
            type="submit"
            class="mt-4 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2"
        >
            Delete client
        </button>
    </form>
</div>
