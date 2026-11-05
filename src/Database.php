<?php

declare(strict_types=1);

namespace App;

use PDO;
use RuntimeException;
use Throwable;

final class Database
{
    private readonly PDO $pdo;

    public function __construct(
        private readonly string $databasePath,
        private readonly string $schemaPath,
    ) {
        $isNewDatabase = $this->databasePath === ':memory:';

        if (!$isNewDatabase) {
            $this->ensureDatabaseDirectoryExists();
            $isNewDatabase = !is_file($this->databasePath);
        }

        $this->pdo = new PDO('sqlite:' . $this->databasePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->pdo->exec('PRAGMA foreign_keys = ON');

        if ($isNewDatabase) {
            $this->initializeSchema();
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    private function initializeSchema(): void
    {
        if (!is_file($this->schemaPath)) {
            throw new RuntimeException(sprintf('Database schema not found at: %s', $this->schemaPath));
        }

        $sql = file_get_contents($this->schemaPath);

        if ($sql === false) {
            throw new RuntimeException(sprintf('Unable to read database schema at: %s', $this->schemaPath));
        }

        try {
            $this->pdo->exec($sql);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Failed to initialize the database schema: ' . $exception->getMessage(),
                0,
                $exception
            );
        }
    }

    private function ensureDatabaseDirectoryExists(): void
    {
        $directory = dirname($this->databasePath);

        if ($directory === '' || $directory === '.' || is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create database directory: %s', $directory));
        }
    }
}
