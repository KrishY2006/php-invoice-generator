<?php

declare(strict_types=1);

$clients = $clients ?? [];
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Clients</h1>
            <p class="mt-1 text-slate-600">Manage the clients you invoice.</p>
        </div>
        <a
            href="/clients/new"
            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700"
        >
            Add client
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <?php if ($clients === []): ?>
            <div class="px-6 py-16 text-center">
                <h2 class="text-lg font-semibold text-slate-900">No clients yet</h2>
                <p class="mt-2 text-sm text-slate-500">Add your first client to start creating invoices.</p>
                <a href="/clients/new" class="mt-5 inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    Add client
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Invoices</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="/clients/<?= e($client['id']) ?>/edit" class="font-medium text-slate-900 hover:text-slate-600">
                                        <?= e($client['name']) ?>
                                    </a>
                                    <?php if (!empty($client['company'])): ?>
                                        <p class="mt-0.5 text-sm text-slate-500"><?= e($client['company']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?= e($client['email'] ?? '') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?= e($client['invoice_count'] ?? 0) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="/clients/<?= e($client['id']) ?>/edit" class="text-sm font-medium text-slate-700 hover:text-slate-900">
                                            Edit
                                        </a>
                                        <form
                                            method="post"
                                            action="/clients/<?= e($client['id']) ?>/delete"
                                            onsubmit="return confirm('Delete this client?');"
                                        >
                                            <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
                                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
