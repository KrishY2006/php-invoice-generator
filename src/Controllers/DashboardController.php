<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Middleware\Auth;
use App\Repositories\InvoiceRepository;
use App\Repositories\SettingsRepository;
use RuntimeException;

final class DashboardController
{
    public function __construct(
        private readonly Config $config,
        private readonly InvoiceRepository $invoices,
        private readonly SettingsRepository $settings,
    ) {
    }

    public function index(): void
    {
        $profile = $this->settings->get();

        render($this->config->basePath(), 'dashboard', [
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'email' => Auth::userEmail() ?? 'Admin',
            'summary' => $this->invoices->summary(gmdate('Y-m'), date('Y-m-d')),
            'currency' => strtoupper((string) ($profile['currency'] ?? 'USD')),
        ]);
    }

    public function exportCsv(): void
    {
        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new RuntimeException('Unable to open the CSV output stream.');
        }

        $filename = 'invoices-' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');

        fputcsv($output, [
            'Invoice Number',
            'Status',
            'Issue Date',
            'Due Date',
            'Currency',
            'Client',
            'Company',
            'Subtotal',
            'Discount',
            'Tax',
            'Total',
            'Paid At',
            'Created At',
            'Notes',
        ], ',', '"', '');

        foreach ($this->invoices->exportRows() as $invoice) {
            fputcsv($output, [
                $this->csvText((string) ($invoice['invoice_number'] ?? '')),
                $this->csvText((string) ($invoice['status'] ?? '')),
                $this->csvText((string) ($invoice['issue_date'] ?? '')),
                $this->csvText((string) ($invoice['due_date'] ?? '')),
                $this->csvText((string) ($invoice['currency'] ?? '')),
                $this->csvText((string) ($invoice['client_name'] ?? '')),
                $this->csvText((string) ($invoice['client_company'] ?? '')),
                $this->amount($invoice['subtotal_cents'] ?? 0),
                $this->amount($invoice['discount_cents'] ?? 0),
                $this->amount($invoice['tax_cents'] ?? 0),
                $this->amount($invoice['total_cents'] ?? 0),
                $this->csvText((string) ($invoice['paid_at'] ?? '')),
                $this->csvText((string) ($invoice['created_at'] ?? '')),
                $this->csvText((string) ($invoice['notes'] ?? '')),
            ], ',', '"', '');
        }

        fclose($output);
    }

    private function amount(mixed $cents): string
    {
        return number_format(((int) $cents) / 100, 2, '.', '');
    }

    private function csvText(string $value): string
    {
        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    }
}
