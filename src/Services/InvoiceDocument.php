<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ClientRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\SettingsRepository;

final class InvoiceDocument
{
    public function __construct(
        private readonly InvoiceRepository $invoices,
        private readonly ClientRepository $clients,
        private readonly SettingsRepository $settings,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $invoice = $this->invoices->find($id);

        if ($invoice === null) {
            return null;
        }

        $useSnapshots = in_array((string) $invoice['status'], ['sent', 'paid', 'cancelled'], true);

        $client = $useSnapshots
            ? $this->decodeObject($invoice['client_snapshot'] ?? null)
            : ($this->clients->find((int) $invoice['client_id']) ?? []);

        $business = $useSnapshots
            ? $this->decodeObject($invoice['seller_snapshot'] ?? null)
            : $this->settings->get();

        if ($client === []) {
            $client = $this->clients->find((int) $invoice['client_id']) ?? [];
        }

        if ($business === []) {
            $business = $this->settings->get();
        }

        $items = array_map(
            fn (array $item): array => $this->normalizeItem($item, $useSnapshots),
            $this->invoices->items($id)
        );

        $invoice['client'] = $client;
        $invoice['business'] = $business;
        $invoice['items'] = $items;
        $invoice['display_status'] = $this->displayStatus($invoice);

        return $invoice;
    }

    /**
     * @param array<string, mixed> $item
     * @return array{
     *     id: int,
     *     description: string,
     *     quantity_hundredths: int,
     *     unit_price_cents: int,
     *     total_cents: int,
     *     position: int
     * }
     */
    private function normalizeItem(array $item, bool $useSnapshot): array
    {
        $description = (string) ($item['description'] ?? '');
        $quantityHundredths = $this->quantityHundredths($item['quantity'] ?? 0);
        $unitPriceCents = (int) ($item['unit_price_cents'] ?? 0);
        $totalCents = (int) ($item['total_cents'] ?? 0);

        if ($useSnapshot) {
            $snapshot = $this->decodeObject($item['item_snapshot'] ?? null);

            if ($snapshot !== []) {
                $description = (string) ($snapshot['description'] ?? $description);
                $quantityHundredths = (int) ($snapshot['quantity_hundredths'] ?? $quantityHundredths);
                $unitPriceCents = (int) ($snapshot['unit_price_cents'] ?? $unitPriceCents);
                $totalCents = (int) ($snapshot['total_cents'] ?? $totalCents);
            }
        }

        return [
            'id' => (int) ($item['id'] ?? 0),
            'description' => $description,
            'quantity_hundredths' => max(0, $quantityHundredths),
            'unit_price_cents' => $unitPriceCents,
            'total_cents' => $totalCents,
            'position' => (int) ($item['position'] ?? 0),
        ];
    }

    private function quantityHundredths(mixed $quantity): int
    {
        if (!is_numeric($quantity)) {
            return 0;
        }

        return (int) round((float) $quantity * 100);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeObject(mixed $json): array
    {
        if (!is_string($json) || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $invoice
     */
    private function displayStatus(array $invoice): string
    {
        $status = (string) ($invoice['status'] ?? 'draft');
        $dueDate = (string) ($invoice['due_date'] ?? '');

        if ($status === 'sent' && $dueDate !== '' && $dueDate < date('Y-m-d')) {
            return 'overdue';
        }

        return $status;
    }
}
