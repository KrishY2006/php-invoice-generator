<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SettingsRepository
{
    private const PROFILE_KEY = 'business_profile';

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<string, string>
     */
    public function get(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1'
        );
        $statement->execute(['key' => self::PROFILE_KEY]);

        $json = $statement->fetchColumn();
        $stored = [];

        if (is_string($json) && $json !== '') {
            $decoded = json_decode($json, true);

            if (is_array($decoded)) {
                $stored = $decoded;
            }
        }

        $defaults = $this->defaults();

        return array_merge($defaults, array_intersect_key($stored, $defaults));
    }

    /**
     * @param array<string, string> $profile
     */
    public function save(array $profile): void
    {
        $profile = array_merge($this->get(), array_intersect_key($profile, $this->defaults()));
        $json = json_encode($profile, JSON_THROW_ON_ERROR);

        $statement = $this->pdo->prepare(
            "INSERT INTO settings (setting_key, setting_value)
             VALUES (:key, :value)
             ON CONFLICT(setting_key) DO UPDATE SET
                setting_value = excluded.setting_value,
                updated_at = strftime('%Y-%m-%dT%H:%M:%fZ', 'now')"
        );

        $statement->execute([
            'key' => self::PROFILE_KEY,
            'value' => $json,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function defaults(): array
    {
        return [
            'name' => '',
            'email' => '',
            'phone' => '',
            'website' => '',
            'address' => '',
            'tax_id' => '',
            'bank_details' => '',
            'logo_path' => '',
            'currency' => 'USD',
            'invoice_prefix' => 'INV',
            'default_tax_rate' => '0.00',
            'payment_terms' => 'Net 30',
            'footer_note' => 'Thank you for your business.',
        ];
    }
}
