<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ClientRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\SettingsRepository;

final class InvoiceLifecycleController
{
    public function __construct(
        private readonly InvoiceRepository $invoices,
        private readonly ClientRepository $clients,
        private readonly SettingsRepository $settings,
    ) {
    }

    public function send(string $id): void
    {
        $invoice = $this->findInvoice($id);

        if ($invoice === null) {
            $this->error('Invoice not found.', '/invoices');
        }

        $invoiceId = (int) $invoice['id'];
        $status = (string) $invoice['status'];

        if ($status !== 'draft') {
            $message = $status === 'sent'
                ? 'This invoice has already been sent.'
                : 'Only draft invoices can be sent.';

            $this->error($message, '/invoices/' . $invoiceId);
        }

        $client = $this->clients->find((int) $invoice['client_id']);

        if ($client === null) {
            $this->error('The invoice client could not be found.', '/invoices/' . $invoiceId);
        }

        $updated = $this->invoices->markSent(
            $invoiceId,
            $client,
            $this->settings->get()
        );

        if (!$updated) {
            $this->error(
                'The invoice could not be sent because its status changed.',
                '/invoices/' . $invoiceId
            );
        }

        $this->success(
            'Invoice ' . (string) $invoice['invoice_number'] . ' marked as sent.',
            '/invoices/' . $invoiceId
        );
    }

    public function pay(string $id): void
    {
        $invoice = $this->findInvoice($id);

        if ($invoice === null) {
            $this->error('Invoice not found.', '/invoices');
        }

        $invoiceId = (int) $invoice['id'];
        $status = (string) $invoice['status'];

        if (!in_array($status, ['sent', 'overdue'], true)) {
            $message = $status === 'draft'
                ? 'Send the invoice before marking it paid.'
                : 'This invoice cannot be marked as paid.';

            $this->error($message, '/invoices/' . $invoiceId);
        }

        if (!$this->invoices->markPaid($invoiceId)) {
            $this->error(
                'The invoice could not be marked as paid because its status changed.',
                '/invoices/' . $invoiceId
            );
        }

        $this->success(
            'Invoice ' . (string) $invoice['invoice_number'] . ' marked as paid.',
            '/invoices/' . $invoiceId
        );
    }

    public function void(string $id): void
    {
        $invoice = $this->findInvoice($id);

        if ($invoice === null) {
            $this->error('Invoice not found.', '/invoices');
        }

        $invoiceId = (int) $invoice['id'];
        $status = (string) $invoice['status'];

        if ($status === 'paid') {
            $this->error('Paid invoices cannot be voided.', '/invoices/' . $invoiceId);
        }

        if ($status === 'cancelled') {
            $this->error('This invoice is already void.', '/invoices/' . $invoiceId);
        }

        if (!$this->invoices->markVoided($invoiceId)) {
            $this->error(
                'The invoice could not be voided because its status changed.',
                '/invoices/' . $invoiceId
            );
        }

        $this->success(
            'Invoice ' . (string) $invoice['invoice_number'] . ' voided.',
            '/invoices/' . $invoiceId
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findInvoice(string $id): ?array
    {
        if (preg_match('/^\d+$/', $id) !== 1) {
            return null;
        }

        $invoiceId = (int) $id;

        if ($invoiceId <= 0) {
            return null;
        }

        return $this->invoices->find($invoiceId);
    }

    private function success(string $message, string $path): never
    {
        $_SESSION['flash_success'] = $message;

        redirect($path);
    }

    private function error(string $message, string $path): never
    {
        $_SESSION['flash_error'] = $message;

        redirect($path);
    }
}
