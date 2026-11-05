<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Services\InvoiceNumber;
use PDO;
use Throwable;

final class InvoiceRepository
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly InvoiceNumber $invoiceNumbers,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $sql = 'SELECT
                    i.id,
                    i.invoice_number,
                    i.status,
                    i.issue_date,
                    i.due_date,
                    i.currency,
                    i.total_cents,
                    i.created_at,
                    c.name AS client_name,
                    c.company AS client_company
                FROM invoices i
                INNER JOIN clients c ON c.id = i.client_id
                ORDER BY i.issue_date DESC, i.id DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array{
     *     outstanding_cents: int,
     *     outstanding_count: int,
     *     paid_this_month_cents: int,
     *     paid_this_month_count: int,
     *     overdue_cents: int,
     *     overdue_count: int
     * }
     */
    public function summary(string $month, string $today): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                COALESCE(SUM(CASE
                    WHEN status IN ('sent', 'overdue') THEN total_cents
                    ELSE 0
                END), 0) AS outstanding_cents,
                COALESCE(SUM(CASE
                    WHEN status IN ('sent', 'overdue') THEN 1
                    ELSE 0
                END), 0) AS outstanding_count,
                COALESCE(SUM(CASE
                    WHEN status = 'paid'
                         AND paid_at IS NOT NULL
                         AND strftime('%Y-%m', paid_at) = :month
                    THEN total_cents
                    ELSE 0
                END), 0) AS paid_this_month_cents,
                COALESCE(SUM(CASE
                    WHEN status = 'paid'
                         AND paid_at IS NOT NULL
                         AND strftime('%Y-%m', paid_at) = :month
                    THEN 1
                    ELSE 0
                END), 0) AS paid_this_month_count,
                COALESCE(SUM(CASE
                    WHEN status IN ('sent', 'overdue')
                         AND due_date < :today
                    THEN total_cents
                    ELSE 0
                END), 0) AS overdue_cents,
                COALESCE(SUM(CASE
                    WHEN status IN ('sent', 'overdue')
                         AND due_date < :today
                    THEN 1
                    ELSE 0
                END), 0) AS overdue_count
             FROM invoices"
        );
        $statement->execute([
            'month' => $month,
            'today' => $today,
        ]);

        $summary = $statement->fetch();

        if ($summary === false) {
            return [
                'outstanding_cents' => 0,
                'outstanding_count' => 0,
                'paid_this_month_cents' => 0,
                'paid_this_month_count' => 0,
                'overdue_cents' => 0,
                'overdue_count' => 0,
            ];
        }

        return [
            'outstanding_cents' => (int) $summary['outstanding_cents'],
            'outstanding_count' => (int) $summary['outstanding_count'],
            'paid_this_month_cents' => (int) $summary['paid_this_month_cents'],
            'paid_this_month_count' => (int) $summary['paid_this_month_count'],
            'overdue_cents' => (int) $summary['overdue_cents'],
            'overdue_count' => (int) $summary['overdue_count'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(): array
    {
        $sql = 'SELECT
                    i.invoice_number,
                    i.status,
                    i.issue_date,
                    i.due_date,
                    i.currency,
                    c.name AS client_name,
                    c.company AS client_company,
                    i.subtotal_cents,
                    i.discount_cents,
                    i.tax_cents,
                    i.total_cents,
                    i.paid_at,
                    i.created_at,
                    i.notes
                FROM invoices i
                INNER JOIN clients c ON c.id = i.client_id
                ORDER BY i.issue_date DESC, i.id DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                client_id,
                invoice_number,
                status,
                issue_date,
                due_date,
                currency,
                notes,
                subtotal_cents,
                tax_cents,
                discount_cents,
                total_cents,
                client_snapshot,
                seller_snapshot,
                paid_at,
                created_at,
                updated_at
             FROM invoices
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $invoice = $statement->fetch();

        return $invoice === false ? null : $invoice;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(int $invoiceId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                invoice_id,
                description,
                quantity,
                unit_price_cents,
                total_cents,
                position,
                item_snapshot
             FROM invoice_items
             WHERE invoice_id = :invoice_id
             ORDER BY position ASC, id ASC'
        );
        $statement->execute(['invoice_id' => $invoiceId]);

        return $statement->fetchAll();
    }

    /**
     * @param array<string, mixed> $clientSnapshot
     * @param array<string, mixed> $sellerSnapshot
     */
    public function markSent(int $id, array $clientSnapshot, array $sellerSnapshot): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE invoices
             SET status = 'sent',
                 client_snapshot = :client_snapshot,
                 seller_snapshot = :seller_snapshot,
                 updated_at = strftime('%Y-%m-%dT%H:%M:%fZ', 'now')
             WHERE id = :id
               AND status = 'draft'"
        );
        $statement->execute([
            'id' => $id,
            'client_snapshot' => json_encode($clientSnapshot, JSON_THROW_ON_ERROR),
            'seller_snapshot' => json_encode($sellerSnapshot, JSON_THROW_ON_ERROR),
        ]);

        return $statement->rowCount() === 1;
    }

    public function markPaid(int $id): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE invoices
             SET status = 'paid',
                 paid_at = strftime('%Y-%m-%dT%H:%M:%fZ', 'now'),
                 updated_at = strftime('%Y-%m-%dT%H:%M:%fZ', 'now')
             WHERE id = :id
               AND status IN ('sent', 'overdue')"
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() === 1;
    }

    public function markVoided(int $id): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE invoices
             SET status = 'cancelled',
                 updated_at = strftime('%Y-%m-%dT%H:%M:%fZ', 'now')
             WHERE id = :id
               AND status IN ('draft', 'sent', 'overdue')"
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() === 1;
    }

    /**
     * @param array{
     *     client_id: int,
     *     invoice_prefix: string,
     *     issue_date: string,
     *     due_date: string,
     *     currency: string,
     *     notes: string|null,
     *     subtotal_cents: int,
     *     discount_cents: int,
     *     tax_cents: int,
     *     total_cents: int,
     *     client_snapshot: array<string, mixed>,
     *     seller_snapshot: array<string, mixed>,
     *     items: array<int, array{
     *         description: string,
     *         quantity_hundredths: int,
     *         unit_price_cents: int,
     *         total_cents: int
     *     }>
     * } $invoice
     * @return array{id: int, invoice_number: string}
     */
    public function save(array $invoice): array
    {
        $this->pdo->beginTransaction();

        try {
            $invoiceNumber = $this->invoiceNumbers->next(
                $invoice['invoice_prefix'],
                (int) substr($invoice['issue_date'], 0, 4)
            );

            $statement = $this->pdo->prepare(
                'INSERT INTO invoices (
                    client_id,
                    invoice_number,
                    status,
                    issue_date,
                    due_date,
                    currency,
                    notes,
                    subtotal_cents,
                    tax_cents,
                    discount_cents,
                    total_cents,
                    client_snapshot,
                    seller_snapshot
                ) VALUES (
                    :client_id,
                    :invoice_number,
                    :status,
                    :issue_date,
                    :due_date,
                    :currency,
                    :notes,
                    :subtotal_cents,
                    :tax_cents,
                    :discount_cents,
                    :total_cents,
                    :client_snapshot,
                    :seller_snapshot
                )'
            );

            $statement->execute([
                'client_id' => $invoice['client_id'],
                'invoice_number' => $invoiceNumber,
                'status' => 'draft',
                'issue_date' => $invoice['issue_date'],
                'due_date' => $invoice['due_date'],
                'currency' => $invoice['currency'],
                'notes' => $invoice['notes'],
                'subtotal_cents' => $invoice['subtotal_cents'],
                'tax_cents' => $invoice['tax_cents'],
                'discount_cents' => $invoice['discount_cents'],
                'total_cents' => $invoice['total_cents'],
                'client_snapshot' => json_encode($invoice['client_snapshot'], JSON_THROW_ON_ERROR),
                'seller_snapshot' => json_encode($invoice['seller_snapshot'], JSON_THROW_ON_ERROR),
            ]);

            $invoiceId = (int) $this->pdo->lastInsertId();

            $itemStatement = $this->pdo->prepare(
                'INSERT INTO invoice_items (
                    invoice_id,
                    description,
                    quantity,
                    unit_price_cents,
                    total_cents,
                    position,
                    item_snapshot
                ) VALUES (
                    :invoice_id,
                    :description,
                    :quantity,
                    :unit_price_cents,
                    :total_cents,
                    :position,
                    :item_snapshot
                )'
            );

            foreach ($invoice['items'] as $position => $item) {
                $itemStatement->execute([
                    'invoice_id' => $invoiceId,
                    'description' => $item['description'],
                    'quantity' => number_format($item['quantity_hundredths'] / 100, 2, '.', ''),
                    'unit_price_cents' => $item['unit_price_cents'],
                    'total_cents' => $item['total_cents'],
                    'position' => $position,
                    'item_snapshot' => json_encode([
                        'description' => $item['description'],
                        'quantity_hundredths' => $item['quantity_hundredths'],
                        'unit_price_cents' => $item['unit_price_cents'],
                        'total_cents' => $item['total_cents'],
                    ], JSON_THROW_ON_ERROR),
                ]);
            }

            $this->pdo->commit();

            return [
                'id' => $invoiceId,
                'invoice_number' => $invoiceNumber,
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
