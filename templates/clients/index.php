<?php

declare(strict_types=1);

$clients = $clients ?? [];
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500">Customer directory</p>
            <p class="mt-1 text-sm text-gray-600">Manage the people and companies you invoice.</p>
        </div>
        <a
            href="/clients/new"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add client
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <?php if ($clients === []): ?>
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                    </svg>
                </span>
                <h2 class="mt-5 text-lg font-semibold text-gray-900">No clients yet</h2>
                <p class="mt-2 max-w-md text-sm text-gray-500">Add your first client to start creating invoices.</p>
                <a href="/clients/new" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add client
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Client</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Email</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Invoices</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($clients as $client): ?>
                            <?php
                            $clientName = (string) ($client['name'] ?? '');
                            $clientInitial = strtoupper(mb_substr($clientName, 0, 1));
                            ?>
                            <tr class="transition-colors hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-100">
                                            <?= e($clientInitial) ?>
                                        </span>
                                        <div class="min-w-0">
                                            <a href="/clients/<?= e($client['id']) ?>/edit" class="font-medium text-gray-900 transition hover:text-indigo-600">
                                                <?= e($clientName) ?>
                                            </a>
                                            <?php if (!empty($client['company'])): ?>
                                                <p class="mt-0.5 truncate text-sm text-gray-500"><?= e($client['company']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= e($client['email'] ?? '') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex min-w-[2rem] items-center justify-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 ring-1 ring-inset ring-gray-200">
                                        <?= e($client['invoice_count'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="/clients/<?= e($client['id']) ?>/edit"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            </svg>
                                            Edit
                                        </a>
                                        <form
                                            method="post"
                                            action="/clients/<?= e($client['id']) ?>/delete"
                                            onsubmit="return confirm('Delete this client?');"
                                        >
                                            <input type="hidden" name="<?= e($csrfFieldName) ?>" value="<?= e($csrfToken) ?>">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
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
            <div class="border-t border-gray-100 bg-gray-50/60 px-6 py-3 text-xs text-gray-500">
                <?= e((string) count($clients)) ?> <?= count($clients) === 1 ? 'client' : 'clients' ?> total
            </div>
        <?php endif; ?>
    </div>
</div>
