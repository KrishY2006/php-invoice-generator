<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;

final class ClientRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $sql = 'SELECT
                    c.id,
                    c.name,
                    c.email,
                    c.company,
                    c.address,
                    c.tax_id,
                    c.created_at,
                    c.updated_at,
                    (SELECT COUNT(*) FROM invoices i WHERE i.client_id = c.id) AS invoice_count
                FROM clients c
                ORDER BY c.name COLLATE NOCASE ASC, c.id ASC';

        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, email, company, address, tax_id, metadata, created_at, updated_at
             FROM clients
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $client = $statement->fetch();

        return $client === false ? null : $client;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $values = $this->normalize($data);

        $statement = $this->pdo->prepare(
            'INSERT INTO clients (name, email, company, address, tax_id, metadata)
             VALUES (:name, :email, :company, :address, :tax_id, NULL)'
        );
        $statement->execute($values);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $values = $this->normalize($data);
        $values['id'] = $id;

        $statement = $this->pdo->prepare(
            "UPDATE clients
             SET name = :name,
                 email = :email,
                 company = :company,
                 address = :address,
                 tax_id = :tax_id,
                 updated_at = strftime('%Y-%m-%dT%H:%M:%fZ', 'now')
             WHERE id = :id"
        );

        return $statement->execute($values);
    }

    public function hasInvoices(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM invoices WHERE client_id = :id');
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function delete(int $id): bool
    {
        if ($this->hasInvoices($id)) {
            return false;
        }

        $statement = $this->pdo->prepare('DELETE FROM clients WHERE id = :id');

        try {
            $statement->execute(['id' => $id]);
        } catch (PDOException $exception) {
            if (
                $exception->getCode() === '23000'
                || str_contains($exception->getMessage(), 'FOREIGN KEY constraint failed')
            ) {
                return false;
            }

            throw $exception;
        }

        return $statement->rowCount() > 0;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string|null>
     */
    private function normalize(array $data): array
    {
        return [
            'name' => trim((string) ($data['name'] ?? '')),
            'email' => $this->nullable($data['email'] ?? null),
            'company' => $this->nullable($data['company'] ?? null),
            'address' => $this->nullable($data['address'] ?? null),
            'tax_id' => $this->nullable($data['tax_id'] ?? null),
        ];
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
