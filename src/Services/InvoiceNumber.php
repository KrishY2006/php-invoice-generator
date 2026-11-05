<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use LogicException;
use PDO;
use RuntimeException;

final class InvoiceNumber
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function next(string $prefix, int $year): string
    {
        if (!$this->pdo->inTransaction()) {
            throw new LogicException('Invoice numbers must be generated inside the invoice transaction.');
        }

        $prefix = $this->normalizePrefix($prefix);

        if ($year < 1900 || $year > 9999) {
            throw new InvalidArgumentException('Invoice year must be between 1900 and 9999.');
        }

        $pattern = $prefix . '-' . $year . '-%';
        $digitStart = strlen($prefix) + strlen((string) $year) + 3;
        $sequenceKey = 'invoice_sequence:' . $prefix . ':' . $year;

        $statement = $this->pdo->prepare(
            'SELECT MAX(CAST(SUBSTR(invoice_number, :digit_start) AS INTEGER))
             FROM invoices
             WHERE invoice_number LIKE :pattern'
        );
        $statement->execute([
            'digit_start' => $digitStart,
            'pattern' => $pattern,
        ]);
        $highestInvoiceSequence = (int) $statement->fetchColumn();

        $statement = $this->pdo->prepare(
            'SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1'
        );
        $statement->execute(['key' => $sequenceKey]);
        $storedSequence = $statement->fetchColumn();

        $storedSequence = is_numeric($storedSequence) ? (int) $storedSequence : 0;
        $currentSequence = max($highestInvoiceSequence, $storedSequence);

        if ($currentSequence >= PHP_INT_MAX) {
            throw new RuntimeException('The invoice number sequence has reached its maximum value.');
        }

        $nextSequence = $currentSequence + 1;

        $statement = $this->pdo->prepare(
            "INSERT INTO settings (setting_key, setting_value)
             VALUES (:key, :value)
             ON CONFLICT(setting_key) DO UPDATE SET
                setting_value = excluded.setting_value,
                updated_at = strftime('%Y-%m-%dT%H:%M:%fZ', 'now')"
        );
        $statement->execute([
            'key' => $sequenceKey,
            'value' => (string) $nextSequence,
        ]);

        return sprintf('%s-%04d-%04d', $prefix, $year, $nextSequence);
    }

    private function normalizePrefix(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix));

        if (preg_match('/^[A-Z0-9-]{1,20}$/', $prefix) !== 1) {
            throw new InvalidArgumentException(
                'Invoice prefix must be 1-20 letters, numbers, or hyphens.'
            );
        }

        return $prefix;
    }
}
