<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;
use InvalidArgumentException;
use RuntimeException;

final class Config
{
    private const REQUIRED_KEYS = [
        'APP_URL',
        'DB_PATH',
        'ADMIN_EMAIL',
        'ADMIN_PASSWORD_HASH',
    ];

    private const OPTIONAL_DEFAULTS = [
        'MAX_LOGO_KB' => '2048',
    ];

    /**
     * @param array<string, string> $values
     */
    private function __construct(
        private readonly string $basePath,
        private readonly array $values,
    ) {
    }

    public static function load(string $basePath): self
    {
        $basePath = rtrim($basePath, '/\\');

        Dotenv::createImmutable($basePath)->safeLoad();

        $missing = [];
        $values = [];

        foreach (self::REQUIRED_KEYS as $key) {
            $value = self::readEnvironmentValue($key);

            if ($value === null) {
                $missing[] = $key;
                continue;
            }

            $values[$key] = $value;
        }

        if ($missing !== []) {
            throw new RuntimeException(sprintf(
                'Missing required environment variable(s): %s. Copy .env.example to .env and set the values.',
                implode(', ', $missing)
            ));
        }

        foreach (self::OPTIONAL_DEFAULTS as $key => $default) {
            $values[$key] = self::readEnvironmentValue($key) ?? $default;
        }

        return new self($basePath, $values);
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function appUrl(): string
    {
        return $this->values['APP_URL'];
    }

    public function adminEmail(): string
    {
        return $this->values['ADMIN_EMAIL'];
    }

    public function adminPasswordHash(): string
    {
        return $this->values['ADMIN_PASSWORD_HASH'];
    }

    public function dbPath(): string
    {
        $path = $this->values['DB_PATH'];

        if ($path === ':memory:' || $this->isAbsolutePath($path)) {
            return $path;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }

    public function maxLogoKb(): int
    {
        return max(1, (int) $this->values['MAX_LOGO_KB']);
    }

    public function logosPath(): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logos';
    }

    public function get(string $key): string
    {
        if (!array_key_exists($key, $this->values)) {
            throw new InvalidArgumentException(sprintf('Unknown configuration key: %s', $key));
        }

        return $this->values[$key];
    }

    private static function readEnvironmentValue(string $key): ?string
    {
        $candidates = [
            getenv($key),
            $_ENV[$key] ?? null,
            $_SERVER[$key] ?? null,
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('#^(?:[A-Za-z]:[\\\\/]|[\\\\/]{2}|[\\\\/])#', $path) === 1;
    }
}
